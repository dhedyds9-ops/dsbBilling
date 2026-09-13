{{--
|--------------------------------------------------------------------------
| Layout Alias: layouts.app → layouts.enterprise  (Admin Portal SSOT)
|--------------------------------------------------------------------------
|
| Backward-compatible alias untuk 33 view modul Keuangan legacy:
|   • cash-accounts/*        (create,edit,index,show)
|   • cash-transactions/*    (create,edit,index,show)
|   • income-categories/*    (create,edit,index,show)
|   • expense-categories/*   (create,edit,index,show)
|   • expense-sharing/*      (create,edit,index,show)
|   • internet-packages/*    (create,edit,index,show)
|   • audit-logs/index
|   • member-incomes/*       (create,edit,index,show)
|   • revenue-sharing/*      (index,show)
|   • reports/*              (trial-balance, profit-loss)
|
| SEMUA modul di atas adalah bagian dari Admin Portal Enterprise.
| JANGAN dihapus — 33 halaman akan error "View [layouts.app] not found".
|
--}}
@extends('layouts.enterprise')
