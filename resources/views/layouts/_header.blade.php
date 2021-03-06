<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- 左侧 -->
            <ul class="navbar-nav d-flex mr-auto">
                <li class="nav-item @if (request()->path() == '/') active @endif">
                    <a class="nav-link" href="{{ url('/') }}">{{ __('Home') }}</a>
                </li>
                <li class="nav-item @if (request()->route()->getName() == 'articles.index') active @endif">
                    <a class="nav-link" href="{{ route('articles.index') }}">{{__('Articles')}}</a>
                </li>
            </ul>

            <!-- 右侧 -->
            <ul class="navbar-nav d-flex ml-auto">
                <form class="navbar-form d-none d-lg-flex mr-2 active" action="/search" method="GET">
                    <input type="text" name="q" class="form-control" value="" placeholder="搜索"></input>
                </form>
                <!-- Authentication Links -->
                <li class="nav-item" v-show="guest">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
                @if (Route::has('register'))
                    <li class="nav-item" v-show="guest">
                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                    </li>
                @endif

                <li class="nav-item" v-show="!guest">
                    <a href="{{ route('user.notifications') }}" class="nav-link"><i class="fa fa-bell"></i></a>
                </li>
                <li class="nav-item" v-show="!guest">
                    <a href="{{ route('user.messages') }}" class="nav-link"><i class="el-icon-message"></i></a>
                </li>

                <li class="nav-item dropdown" style="display: none;" v-show="!guest">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="avatar-20" v-bind:alt="username" v-bind:src="avatar">
                        <span v-text="username"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                              style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
