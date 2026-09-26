@extends('errors.layout')

@section('code', '419')
@section('badge-class', 'badge-amber')
@section('badge-text', app()->getLocale() === 'ar' ? 'انتهت الجلسة' : 'Session Expired')

@section('title', app()->getLocale() === 'ar' ? 'انتهت صلاحية جلسة العمل' : 'Page Session Expired')

@section('message')
    {{ app()->getLocale() === 'ar' ? 'انتهت صلاحية الجلسة أو رمز الأمان بسبب الخمول لفترة طويلة. يُرجى تحديث الصفحة أو إعادة تسجيل الدخول.' : 'Your security token has expired due to session inactivity. Please refresh the page and sign in again.' }}
@endsection
