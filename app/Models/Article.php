<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 文章模型
 * @property int $id ID
 * @property int $user_id 作者ID
 * @property int $category_id 栏目ID
 * @property string $title 标题
 * @property string $description 描述
 * @property string $thumb_path 缩略图
 * @property int $status 状态
 * @property int $order 排序
 * @property int $views 查看次数
 * @property int $comment_count 评论次数
 * @property int $support_count 点赞次数
 * @property int $collection_count 收藏次数
 * @property array $metas Meta信息
 * @property array $extra 扩展信息
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property Tag[] $tags
 * @property Category $category
 * @property User $user
 * @property ArticleDetail $detail 文章详情
 *
 * @property string $tag_values 文章标签
 * @property-read string $link 文章Url
 * @property-read boolean $isApproved 是否已审核
 * @property-read boolean $hasPending 是否待审核
 * @property-read boolean $thumb 缩略图Url
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Article approved()
 * @method static \Illuminate\Database\Eloquent\Builder|Article byCategoryId($categoryId)
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Article extends Model
{
    use Traits\HasTaggable;
    use Traits\HasDateTimeFormatter;
    use SoftDeletes;

    const STATUS_UNAPPROVED = 0b0;//待审核
    const STATUS_APPROVED = 0b1;//已审核
    const STATUS_REJECTED = 0b10;//拒绝

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'articles';

    /**
     * 允许批量赋值的属性
     * @var array
     */
    public $fillable = [
        'user_id', 'category_id', 'title', 'thumb_path', 'status', 'description', 'order', 'tag_values',
        'metas'
    ];

    /**
     * 追加字段
     * @var string[]
     */
    protected $appends = [
        'tag_values',
        'thumb'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 0b1,
        'views' => 0,
        'order' => 0,
        'comment_count' => 0,
        'support_count' => 0,
        'collection_count' => 0,
    ];

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'metas' => 'array',
        'views' => 'int',
        'order' => 'int',
        'comment_count' => 'int',
        'support_count' => 'int',
        'collection_count' => 'int',
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the detail relation.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {
        return $this->hasOne(ArticleDetail::class);
    }

    /**
     * 关联到栏目
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the article stop words.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stopWords()
    {
        return $this->hasOne(ArticleMod::class);
    }

    /**
     * 查找指定栏目下的文章
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategoryId($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', static::STATUS_APPROVED);
    }

    /**
     * 是否待审核
     * @return boolean
     */
    public function getIsUnapprovedAttribute()
    {
        return $this->status == static::STATUS_UNAPPROVED;
    }

    /**
     * 是否已经审核
     * @return boolean
     */
    public function getIsApprovedAttribute()
    {
        return $this->status == static::STATUS_APPROVED;
    }

    /**
     * 获取缩略图
     * @return string
     */
    public function getThumbAttribute()
    {
        if (!empty($this->attributes['thumb_path'])) {
            if (Str::contains($this->attributes['thumb_path'], '//')) {
                return $this->attributes['thumb_path'];
            } else {
                return Storage::cloud()->url($this->attributes['thumb_path']);
            }
        }
        return null;
    }

    /**
     * 上一篇
     * @return \Illuminate\Database\Eloquent\Builder|Model|object
     */
    public function getPreviousAttribute()
    {
        return static::approved()->byCategoryId($this->category_id)->where('id', '<', $this->id)->first();
    }

    /**
     * 下一篇
     * @return \Illuminate\Database\Eloquent\Builder|Model|object
     */
    public function getNextAttribute()
    {
        return static::approved()->byCategoryId($this->category_id)->where('id', '>', $this->id)->first();
    }

    /**
     * 获取 访问Url
     * @return string
     */
    public function getLinkAttribute()
    {
        return route('articles.show', ['article' => $this]);
    }

    /**
     * 设置已审核
     */
    public function setApproved()
    {
        $this->status = static::STATUS_APPROVED;
        $this->saveQuietly();
    }

    /**
     * 设置审核拒绝通过
     */
    public function setRejected()
    {
        $this->status = static::STATUS_REJECTED;
        $this->saveQuietly();
    }

    /**
     * 获取状态Label
     * @return string[]
     */
    public static function getStatusLabels()
    {
        return [
            static::STATUS_UNAPPROVED => '待审核',
            static::STATUS_APPROVED => '已审核',
            static::STATUS_REJECTED => '拒绝',
        ];
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots()
    {
        return [
            static::STATUS_UNAPPROVED => 'info',
            static::STATUS_APPROVED => 'success',
            static::STATUS_REJECTED => 'error',
        ];
    }

    /**
     * 获取最新的10条资讯
     * @param int $limit
     * @param int $cacheMinutes
     * @return mixed
     */
    public static function latest($limit = 10, $cacheMinutes = 15)
    {
        $ids = Cache::store('file')->remember('articles:latest:ids', now()->addMinutes($cacheMinutes), function () use ($limit) {
            return static::approved()->orderByDesc('id')->limit($limit)->pluck('id');
        });
        return $ids->map(function ($id) {
            return static::find($id);
        });
    }

    /**
     * 获取缓存的文章总数
     * @param int $cacheMinutes
     * @return mixed
     */
    public static function getTotal($cacheMinutes = 60)
    {
        return Cache::remember('articles:total', now()->addMinutes($cacheMinutes), function () {
            return static::query()->count();
        });
    }
}
