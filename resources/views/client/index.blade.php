@extends('client.master')

@section('content')
    <!-- Login Frm -->
    @include('client.related.slide')
    @include('client.related.news')
    {{-- LAUNCHER --}}
    <div class="cards slide-bottom time-300" style="position: fixed; right: 10px; top: calc(50% - 140px);">
        <a href="{{$config['launcher_download_url']}}" target="_BLANK" class="right">
            <img src="assets/img/3.png">
        </a>
        <a href="{{$config['uc_download_url']}}" class="right" download>
            <img src="assets/img/uc.jpg">
        </a>
        <a href="/account?type=checkin" class="right">
            <img src="assets/img/diem_danh_nhan_qua.png">
        </a>
    </div>
    <section class="box guia">
        <div class="title-new">
            <h1>Hướng dẫn</h1>
        </div>
        <ul class="biglist animElement slide-left">
            <li>
                <a href="{{route('view-game-guide','1')}}">
                    <i class="icon-controls"></i>
                    <strong>Điều Khiển</strong>
                    <p>Sử dụng các phím mũi tên để di chuyển nhân vật và điều chỉnh góc, phím cách dùng để bắn.
                    </p>
                </a>
            </li>
            <li>
                <a href="{{route('view-game-guide','2')}}">
                    <i class="icon-fight-lab"></i>
                    <strong>Đo màn hình</strong>
                    <p>Hướng dẫn đo màn hình và lực bắn để bắn chính xác.</p>
                </a>
            </li>

        </ul>
    </section>
    @include('client.related.item')

@endsection


