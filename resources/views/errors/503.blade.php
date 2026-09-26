@extends('errors.layout')

@section('code', '503')
@section('badge-class', 'badge-slate')
@section('badge-text', app()->getLocale() === 'ar' ? 'تحت الصيانة' : 'Maintenance')

@section('title', app()->getLocale() === 'ar' ? 'الخدمة قيد الصيانة المجدولة' : 'Service Under Maintenance')

@section('message')
    {{ app()->getLocale() === 'ar' ? 'نقوم حالياً بإجراء تحسينات مجدولة على النظام لخدمتكم بشكل أفضل. سنعود للعمل قريباً.' : 'We are performing scheduled maintenance and updates to improve your hotel management experience. We will be back online shortly.' }}
@endsection
