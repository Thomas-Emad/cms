@extends('errors.layout')

@section('code', '403')
@section('badge-class', 'badge-amber')
@section('badge-text', app()->getLocale() === 'ar' ? 'غير مصرح بالدخول' : 'Access Restricted')

@section('title', app()->getLocale() === 'ar' ? 'غير مصرح لك بالوصول إلى هذه الصفحة' : 'Access Denied / Restricted')

@section('message')
    {{ $exception?->getMessage() ?: (app()->getLocale() === 'ar' ? 'ليس لديك الصلاحيات الكافية للوصول إلى هذا الفرع أو القسم الإداري. إذا كنت مسجلاً بحساب آخر، يُرجى تسجيل الخروج بالأسفل للتبديل لحسابك الصحيح.' : 'You do not have permission to view or manage this hotel resource or branch. If you are signed into an account with insufficient privileges, please sign out below to switch accounts.') }}
@endsection
