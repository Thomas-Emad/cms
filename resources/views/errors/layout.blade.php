<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - @yield('title') | {{ config('app.name', 'Smarttel Hospitality') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e2e8f0; }
        html { line-height: 1.5; font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; background-color: #020617; color: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; overflow-x: hidden; }
        .ambient-glow-1 { position: absolute; top: -100px; left: -100px; width: 400px; height: 400px; border-radius: 9999px; background: rgba(16, 185, 129, 0.08); filter: blur(80px); pointer-events: none; }
        .ambient-glow-2 { position: absolute; bottom: -100px; right: -100px; width: 400px; height: 400px; border-radius: 9999px; background: rgba(245, 158, 11, 0.08); filter: blur(80px); pointer-events: none; }
        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 1.5rem; position: relative; z-index: 10; }
        .header { display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; }
        .logo-box { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit; }
        .logo-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(16, 185, 129, 0.2)); border: 1px solid rgba(245, 158, 11, 0.4); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fbbf24; }
        .main-card { width: 100%; max-width: 34rem; margin: 3rem auto; background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(30, 41, 59, 0.9); border-radius: 1.25rem; padding: 2.25rem; backdrop-filter: blur(16px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .badge { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid; }
        .badge-amber { border-color: rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .badge-slate { border-color: rgba(100, 116, 139, 0.4); background: rgba(51, 65, 85, 0.3); color: #94a3b8; }
        .badge-rose { border-color: rgba(244, 63, 94, 0.4); background: rgba(244, 63, 94, 0.1); color: #fb7185; }
        .status-number { font-family: monospace; font-size: 2rem; font-weight: 800; color: #94a3b8; }
        .user-box { margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.75rem; background: rgba(2, 6, 23, 0.6); border: 1px solid rgba(30, 41, 59, 0.8); display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; gap: 0.75rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.15s ease; border: none; }
        .btn-rose { background: linear-gradient(to right, #e11d48, #be123c); color: white; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.3); }
        .btn-rose:hover { background: linear-gradient(to right, #f43f5e, #e11d48); }
        .btn-amber { background: linear-gradient(to right, #d97706, #b45309); color: white; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3); }
        .btn-amber:hover { background: linear-gradient(to right, #f59e0b, #d97706); }
        .btn-slate { background: rgba(30, 41, 59, 0.8); color: #f1f5f9; border: 1px solid rgba(51, 65, 85, 0.8); }
        .btn-slate:hover { background: rgba(51, 65, 85, 0.9); }
        .footer { text-align: center; padding: 1.5rem; font-size: 0.75rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <header class="container header">
        <a href="{{ url('/') }}" class="logo-box">
            <div class="logo-icon">H</div>
            <div>
                <div style="font-weight: 700; font-size: 0.95rem; color: #f8fafc;">{{ config('app.name', 'Smarttel Hospitality') }}</div>
                <div style="font-size: 0.75rem; color: #94a3b8;">{{ app()->getLocale() === 'ar' ? 'منصة إدارة الفنادق الذكية' : 'Luxury Hospitality Suite' }}</div>
            </div>
        </a>

        <div>
            @if(app()->getLocale() === 'ar')
                <a href="{{ url('/locale/en') }}" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none; border: 1px solid #334155; padding: 0.25rem 0.6rem; border-radius: 0.375rem;">English</a>
            @else
                <a href="{{ url('/locale/ar') }}" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none; border: 1px solid #334155; padding: 0.25rem 0.6rem; border-radius: 0.375rem;">العربية</a>
            @endif
        </div>
    </header>

    <main class="container">
        <div class="main-card">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(30, 41, 59, 0.8); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                <div class="badge @yield('badge-class', 'badge-slate')">
                    <span style="width: 8px; height: 8px; border-radius: 9999px; background: currentColor; display: inline-block;"></span>
                    <span>@yield('badge-text')</span>
                </div>
                <div class="status-number">@yield('code')</div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h1 style="font-size: 1.6rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #ffffff;">@yield('title')</h1>
                <p style="font-size: 0.875rem; line-height: 1.6; color: #94a3b8; margin: 0;">@yield('message')</p>
            </div>

            @auth
                <div class="user-box">
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 600; color: #f1f5f9;">
                            {{ auth()->user()->name }}
                            <span style="display: inline-block; margin-left: 0.35rem; margin-right: 0.35rem; font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                                {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Hotel Admin' }}
                            </span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">
                            {{ auth()->user()->email }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-rose" style="padding: 0.4rem 0.85rem; font-size: 0.75rem;">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Log Out' }}
                        </button>
                    </form>
                </div>
            @endauth

            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem;">
                @auth
                    @if(trim($__env->yieldContent('code')) === '403')
                        <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                            @csrf
                            <button type="submit" class="btn btn-rose" style="width: 100%;">
                                {{ app()->getLocale() === 'ar' ? 'تسجيل الخروج والتبديل لحساب آخر' : 'Sign Out & Switch Account' }}
                            </button>
                        </form>
                    @endif

                    <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.platform.dashboard') : route('admin.dashboard') }}" class="btn btn-slate">
                        {{ app()->getLocale() === 'ar' ? 'الذهاب إلى لوحة التحكم' : 'Go to Dashboard' }}
                    </a>
                @endauth

                <a href="{{ url('/') }}" class="btn btn-amber">
                    {{ app()->getLocale() === 'ar' ? 'العودة للصفحة الرئيسية' : 'Return to Home' }}
                </a>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(30, 41, 59, 0.8); padding-top: 1rem; margin-top: 0.5rem; font-size: 0.75rem;">
                    <a href="javascript:history.back()" style="color: #94a3b8; text-decoration: none;">
                        ← {{ app()->getLocale() === 'ar' ? 'الرجوع للصفحة السابقة' : 'Go back to previous page' }}
                    </a>

                    @guest
                        <a href="{{ route('login') }}" style="color: #fbbf24; text-decoration: underline;">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول للموظفين' : 'Staff Sign In' }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>© {{ date('Y') }} {{ config('app.name', 'Smarttel Hospitality') }}. All rights reserved.</p>
    </footer>
</body>
</html>
