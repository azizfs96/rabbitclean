<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $websetting = App\Models\WebSetting::first();
    @endphp
    <link rel="icon" type="image/png" href="{{ $websetting?->websiteFaviconPath ?? asset('web/favIcon.png') }}">
    <title>{{ $websetting->title ?? config('app.name') }}</title>
    <!-- Fonts -->

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('web/css/all.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/bootstrap.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/select2.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/style.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/custom.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/datatables.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('web/css/toastr.min.css') }}" type="text/css">

</head>

<style>
    .nav-profile-box {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .language-image {
        background: #29aae130;
        padding: 8px 8px 4px 8px;
        border-radius: 4px;
    }

    .language-image div {
        margin-top: 2px;
        font-weight: 400;
    }

    .language-image a img {
        width: 28px;
    }

    .nav-profile-box.dropdown-toggle::after {
        display: none;
    }

    .badgeButtonBox .dropdown-toggle::after {
        display: none;
    }
    .dropdown-toggle {
    white-space: nowrap;
}

</style>

<body>

    {{-- <div class="preload">
        <div class="flexbox">
            <div>
                <img src="{{ asset('images/loader/GoldStar-Loader.gif') }}" alt="">
            </div>
        </div>
    </div> --}}

    @include('layouts.partials.sidebar')



    <div class="main-content">

        <div class="main-header shadow-sm">

            @php
                use App\Models\Language;
                $languages = Language::All();
                $language = Language::where('name', app()->getLocale())->first();
            @endphp

            <div class="user-profile-box dropdown mx-3">
                <div class="nav-profile-box dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <div class="profile-image language-image d-flex gap-2">
                        <a href="#">
                            <img class="" src="{{ $language?->file }}" alt="{{ $language?->title }}">
                        </a>
                        <div>{{ $language?->title }}</div>
                    </div>
                </div>
                <div class="dropdown-menu profile-item">
                    @foreach ($languages as $lang)
                        <a href="{{ route('change.local', 'ln=' . $lang->name) }}" class="dropdown-item">
                            <img src="{{ $lang->file }}" alt="{{ $lang->title }}" width="20"
                                class="me-2"> {{ $lang->title }}
                        </a>
                    @endforeach
                </div>
            </div>


            <div class="btn-group dropdown">
                <button type="button" class="notificationBell dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell Icon"></i>
                    <div id="total">0</div>
                </button>
                <div class="dropdown-menu dropdown-menu-right" id="notification">
                    <a class="dropdown-item" href="#">
                        <div class="message"></div>
                        <div class="time"></div>
                    </a>
                </div>
            </div>
        </div>

        @yield('content')

    </div>

    <script src="{{ asset('web/js/jquery.min.js') }}"></script>
    <script src="{{ asset('web/js/popper.js') }}"></script>
    <script src="{{ asset('web/js/sweet-alert.js') }}"></script>
    <script src="{{ asset('web/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('web/js/select2.min.js') }}"></script>

    <script src="{{ asset('web/js/argon.js') }}"></script>
    <script src="{{ asset('web/js/main.js') }}"></script>
    <script src="{{ asset('web/js/datatables.min.js') }}"></script>
    <script src="{{ asset('web/js/toastr.min.js') }}"></script>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        var pusher = new Pusher("{{ config('app.pusher_key') }}", {
            cluster: "{{ config('app.pusher_cluster') }}"
        });

        var channel = pusher.subscribe('popup-channel');
        channel.bind('order-notification', function(data) {
            toastr.success(JSON.stringify(data.message))
            showNotifications()
        });
    </script>

    <script>
        const showNotifications = function() {
            var totalNumber = $('#total');

            $.ajax({
                type: 'GET',
                url: "{{ route('new.orders') }}",
                dataType: 'json',
                success: function(response) {
                    $('#total').text(response.data.orders.length)
                    $('#notification').empty()
                    $.each(response.data.orders, function(key, value) {
                        $('#notification').append(
                            "<a class='dropdown-item' href='/orders/" + value.id +
                            "'><div class='message'>New Order From <strong>" + value.customer
                            .user.name + "</strong> Order ID: " + value.order_code +
                            "</div> <div class='time'>" + value.ordered_at + "</div></a>"
                        );
                    })
                },
                error: function(e) {
                    $('#notification').empty()
                    $("#notification").html(e.responseText);
                }
            });
        }
        showNotifications()

        $('.visitorMessage').click(function(e) {
            e.preventDefault()
            Swal.fire(
                'Access Denied!',
                "You don't have permission to create, update or delete because you are visitor.",
                'warning'
            )
        })
    </script>

    @if (session('visitor'))
        <script>
            Swal.fire(
                'You are visitor.',
                'Sorry, you can\'t anything create, update and delete.',
                'question'
            )
        </script>
    @endif

    @if (session('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            })
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 2000
            })
        </script>
    @endif

    @stack('scripts')

    <script>
        $('#language').change(function() {
            var url = "{{ route('change.local') }}";
            var lan = $(this).val();
            window.location.href = url + '?ln=' + lan;
        });

        const lang = '{{ session()->get('local') }}';
        if (lang === 'ar') {
            $('#myTable').DataTable({
                language: {
                    'paginate': {
                        'previous': '<i class="fas fa-angle-double-left"></i>',
                        'next': '<i class="fas fa-angle-double-right"></i>'
                    },
                    "lengthMenu": "يعرض _MENU_ إدخالات",
                    "zeroRecords": "لم يتم العثور على سجلات مطابقة",
                    "info": "إظهار _START_ إلى _END_ من أصل _TOTAL_ إدخالات",
                    "infoEmpty": "لا توجد بيانات متوفرة في الجدول",
                    "infoFiltered": "(تمت تصفيته من إجمالي _MAX_ إدخالات)",
                    "search": "يبحث:",
                }
            });
        } else {
            $('#myTable').DataTable({
                language: {
                    'paginate': {
                        'previous': '<i class="fas fa-angle-double-left"></i>',
                        'next': '<i class="fas fa-angle-double-right"></i>'
                    },
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select One",
            });
        });

        //delete confirm sweet alert
        $('.delete-confirm').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00B894',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        });
    </script>
</body>

</html>
