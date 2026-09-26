@extends('errors.layout')

@section('code', '404')
@section('badge-class', 'badge-slate')
@section('badge-text', app()->getLocale() === 'ar' ? 'الصفحة غير موجودة' : 'Not Found')

@section('title', app()->getLocale() === 'ar' ? 'الصفحة المطلوبة غير موجودة' : 'Page Not Found')

@section('message')
    {{ $exception?->getMessage() ?: (app()->getLocale() === 'ar' ? 'الصفحة أو الوجهة التي تبحث عنها غير متوفرة أو ربما تم تغيير عنوانها أو حذفها.' : 'The requested page or resource could not be found. It may have been moved, renamed, or is temporarily unavailable.') }}
@endsection
