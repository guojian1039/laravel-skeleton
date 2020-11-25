<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class ApiController
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ApiController extends Controller
{
    /**
     * Tag Ajax加载
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|null
     */
    public function users(Request $request)
    {
        $query = \App\Models\User::query()->select(['id', 'username'])->orderByDesc('id');
        $q = $request->get('q');
        if (mb_strlen($q) >= 2) {
            $query->where('username', 'LIKE', '%' . $q . '%');
        }
        return $query->paginate(10);
    }
}
