<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap.min.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/lineicons.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/materialdesignicons.min.css') }}" rel="stylesheet"
    type="text/css" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/fullcalendar.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/main.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/dark-mode.css') }}" />

<link href="{{ asset('backend/assets/css/tailwind.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/assets/css/datatable.tailwind.min.css') }}" rel="stylesheet">
<script src="{{ asset('backend/assets/jquery/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('backend/assets/jquery/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/assets/jquery/dataTables.tailwindcss.min.js') }}"></script>

<!--jika pake bootstrap-->
<!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>-->

<!--data table responsive-->
<link rel="stylesheet" href="{{ asset('backend/assets/css/data-table.responsive.bootstrap5.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
    .sidebar {
        transition: transform 0.3s ease;
    }

    .sidebar.hidden {
        transform: translateX(-100%);
    }

    @media (min-width: 768px) {
        .sidebar.hidden {
            transform: translateX(0);
        }
    }
</style>