<?php
namespace App\DataTables;

use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('DT_RowIndex', function ($user) {
                return '';
            })
            ->editColumn('name', function ($user) {
                return view('admin.pages.users.partials.name-column', compact('user'));
            })
            ->editColumn('roles', function ($user) {
                return view('admin.pages.users.partials.roles-column', compact('user'));
            })
            ->editColumn('is_active', function ($user) {
                return view('admin.pages.users.partials.status-column', compact('user'));
            })
            ->editColumn('last_login_time', function ($user) {
                return $user->last_login_time ? $user->last_login_time->format('Y-m-d H:i') : 'Never';
            })
            ->addColumn('actions', function ($user) {
                return view('admin.pages.users.partials.actions-column', compact('user'));
            })
            ->rawColumns(['name', 'roles', 'is_active', 'actions']);
    }

    public function query(User $model)
    {
        return $model->newQuery()->with('roles');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('usersTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>')
            ->orderBy(1)
            ->responsive(true)
            ->buttons(
                Button::make('reload')
            );
    }

    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title('#'),
            Column::make('name')->title('Name'),
            Column::make('username')->title('Username'),
            Column::make('email')->title('Email'),
            Column::make('roles')->title('Roles'),
            Column::make('is_active')->title('Status'),
            Column::make('last_login_time')->title('Last Login'),
            Column::computed('actions')->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }
}
