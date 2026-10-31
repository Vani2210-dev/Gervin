<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body class="dark:bg-neutral-800 bg-neutral-100">

    <!-- ..::  header area start ::.. -->
    <x-sidebar />
    <!-- ..::  header area end ::.. -->

    <main class="dashboard-main active">

        <!-- ..::  navbar start ::.. -->
        <x-navbar />
        <!-- ..::  navbar end ::.. -->
        <script>
            (function() {
                if (window.innerWidth >= 1024) {
                    var saved = localStorage.getItem('sidebar-collapsed');
                    if (saved === 'false') {
                        document.querySelector('.sidebar')?.classList.remove('active');
                        document.querySelector('.dashboard-main')?.classList.remove('active');
                        document.querySelector('.sidebar-toggle')?.classList.remove('active');
                    } else if (saved === 'true') {
                        document.querySelector('.sidebar')?.classList.add('active');
                        document.querySelector('.dashboard-main')?.classList.add('active');
                        document.querySelector('.sidebar-toggle')?.classList.add('active');
                    }
                }
            })();
        </script>
        <div class="dashboard-main-body">
            
            <!-- ..::  breadcrumb  start ::.. -->
            <x-breadcrumb title='{{ isset($title) ? $title : "" }}' subTitle='{{ isset($subTitle) ? $subTitle : "" }}' />
            <!-- ..::  header area end ::.. -->

            @yield('content')
        
        </div>

    </main>

    <!-- ..::  scripts  start ::.. -->
    <x-script  script='{!! isset($script) ? $script : "" !!}' />
    @stack('scripts')
    <!-- ..::  scripts  end ::.. -->

</body>

</html>