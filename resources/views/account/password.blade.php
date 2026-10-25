@extends('layouts.app')
@section('title', 'Reset your password — Common Ground')
@section('content')
<div class="container"><div class="panel auth-panel"><span class="eyebrow">ACCOUNT RECOVERY</span><h1 style="margin-top:17px">{{ $reset ? 'Choose a new password.' : 'Find your way back.' }}</h1><p>{{ $reset ? 'Use at least 10 characters. Resetting your password revokes your API tokens.' : 'We’ll send a secure reset link to your email address.' }}</p><form class="form-stack" method="post" action="{{ $reset ? '/reset-password' : '/forgot-password' }}">@csrf
@if($reset)<input type="hidden" name="token" value="{{ $token }}">@endif
<label>Email address<input name="email" type="email" value="{{ old('email', request('email')) }}" required autocomplete="email"></label>
@if($reset)<label>New password<input name="password" type="password" minlength="10" maxlength="128" required autocomplete="new-password"></label><label>Confirm password<input name="password_confirmation" type="password" minlength="10" maxlength="128" required autocomplete="new-password"></label>@endif
<button class="button" type="submit">{{ $reset ? 'Reset password' : 'Send reset link' }}</button></form><p><a href="/login">Back to sign in</a></p></div></div>
@endsection
