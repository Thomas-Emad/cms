@extends('errors.layout')

@section('code', '500')
@section('badge-class', 'badge-rose')
@section('badge-text', app()->getLocale() === 'ar' ? 'خطأ في الخادم' : 'Server Error')

@section('title', app()->getLocale() === 'ar' ? 'حدث خطأ غير متوقع في الخادم' : 'Internal Server Error')

@section('message')
    {{ app()->getLocale() === 'ar' ? 'واجهت خوادمنا مشكلة غير متوقعة أثناء معالجة طلبك. تم إرسال تنبيه لفريق التطوير. يُرجى إعادة المحاولة لاحقاً.' : 'An internal error occurred on our servers while processing this request. Our technical team has been notified. Please try again shortly.' }}
@endsection
