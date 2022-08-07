<script type="text/javascript">
    $(document).ready(function() {

        $('.tags').on('click', 'a', function(e) {
            //console.log(this);
            var val = $(this).attr('href').slice(1);
            $(this).parent().find('a').removeClass('selected');
            $(this).toggleClass('selected');
            if (val != "paypal" && val != "stripe" && val != "card" && val != "ex1") {
                $('#valor').val(val);
            }
            e.preventDefault();
        });
    });
</script>
<section class="box register">
    <div class="title-new">
        <h1 style="color: #c3332a">NẠP TIỀN</h1>
    </div>
    <div class="tabsContent">
        <div class="active biglist">
            <div class="tab">
                <button class="tablinks active" onclick="openPage(event, 'napthe')">NẠP THẺ</button>
                <button class="tablinks" onclick="openPage(event, 'napmomo')">NẠP MOMO</button>
                <button class="tablinks" onclick="openPage(event, 'napatm')">NẠP ATM</button>
            </div>

            <div id="napthe" class="tabcontent" style="display: block;">
                <div class="alert alert-error">
                    Chú ý ! Chọn đúng mã thẻ, thẻ sai mệnh giá sẽ bị mất.
                </div>
                <div class="alert alert-error">
                    Chú ý ! Quá trình nạp sẽ mất từ 7s - 1phút.
                </div>
                <div class="alert alert-error">
                    (Tỉ lệ nạp: 10.000đ thẻ cào = {{$heSoTheCao * 10000}} coin, 10.000đ ATM & Momo = {{$heSoATM * 10000}} coin)
                </div>
                <div class="list-card-recharge">
                    <h1 class="title">
                        CHỌN LOẠI THẺ :
                    </h1>
                    <div class="tags">
                        <a title="Thẻ Vietel" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(1)"
                                 src="/assets/img/pay/viettel.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(1)">
                        </a>

                        <a title="Thẻ Mobifone" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(2)"
                                 src="/assets/img/pay/mobiphone.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(2)">
                        </a>

                        <a title="Thẻ VinaPhone" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(3)"
                                 src="/assets/img/pay/vinaphone.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(3)">
                        </a>

                        <br>

                        <a title="Thẻ Gate" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(4)"
                                 src="/assets/img/pay/gate.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(4)">
                        </a>

                        <a title="Thẻ Zing" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(7)"
                                 src="/assets/img/pay/zing.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(7)">
                        </a>

                        <a title="Thẻ Vietnamobile" class="hlk_selectCard" href="javascript:;">
                            <img class="img-thumbnail" onclick="setTypecard(6)"
                                 src="/assets/img/pay/vietnamobile.png">
                            <input type="radio" name="rdoCardType" class="ratio_deposite" onclick="setTypecard(8)">
                        </a>

                    </div>
                    <div>
                    <span class="typeCardError" style="width: 49%;color: red!important;display: none">Vui lòng chọn loại thẻ</span>
                    </div>
                </div>
                <form id="rechargeForm" class="account">
                    <div style="padding:30px;">
                        <div class="selects">
                            <label class="control-label" for="email_login">Mệnh giá:</label>
                            <select id="menhgia_the" name="menhgia_the" autocomplete="off" class="form-control">
                                <option value="">-- Chọn mệnh giá--</option>
                                <option value="10000">10,000 vnd</option>
                                <option value="20000">20,000 vnd</option>
                                <option value="30000">30,000 vnd</option>
                                <option value="50000">50,000 vnd</option>
                                <option value="100000">100,000 vnd</option>
                                <option value="200000">200,000 vnd</option>
                                <option value="300000">300,000 vnd</option>
                                <option value="500000">500,000 vnd</option>
                                <option value="1000000">1,000,000 vnd</option>
                            </select>
                        </div>
                        <span class="amountError" style="width: 49%;color: red!important;display: none">Vui lòng chọn mệnh giá thẻ</span>

                        <label style="margin: 5px 0!important;">
                            <span>Nhập Serial & Mã thẻ</span>
                            <div style="width: 100%;margin: 5px 0!important;">
                                <input style="width: 49%" id="txtSerial" placeholder="Nhập số serial"
                                       autocomplete="off" required="">
                                <input style="width: 49%" id="txtPasscard" placeholder="Nhập mã thẻ"
                                       autocomplete="off" required="">
                            </div>
                            <div style="">
                                <span class="serialError" style="width: 49%;color: red!important;display: none">Vui lòng nhập số Seri</span>
                                <span class="passcardError" style="width: 49%;color: red!important;display: none">Vui lòng nhập mã thẻ</span>
                            </div>
                        </label>


                        <label style="margin: 5px 0!important;">
                            <span>Xác nhận Captcha</span>
                            <div style="width: 100%;margin: 5px 0!important;">
                                <div class="wrapper-captcha">
                                    <input type="text" id="txtCaptcha" style="width:200px;" placeholder="Nhập chuỗi bên cạnh"
                                           autocomplete="off" required="">
                                    <img id="captcha_img_src" src="{{captcha_src()}}"/>
                                </div>
                                <div id="regacc_txtcode_tooltip" class="error-check" style="display:none;">
                                </div>
                            </div>
                        </label>
                        <div class="errors-recharge-form" style="display: none;color: red"></div>

                        <div class="button-functional-account">
                            <a id="rechargeCardBtn" class="item"
                               style="background-color: rgb(245,98,0); border-color: rgb(250,83,0);">NẠP THẺ
                            </a>
                        </div>
                    </div>
                </form>
            </div>

                <div id="napmomo" class="tabcontent" style="display: none;">
                    <div class="card-body">
                        {!! $momoQr !!}
						<br>
                        Chuyển khoản MOMO tới số : <b>{{$config['payment_momo_phone']}}</b> <br>
                        Tên Tài Khoản : <b>{{$config['payment_momo_author']}}</b><br>
                        Nội dung : <b>{{Auth::guard('member')->user()->Email}}</b><br>
                        <br>
						<b>
                            <font color="blue">Vui lòng kích hoạt 2FA để bảo về tài khoản.</font>
						<br />
						<br />
                        <b>
                            <font color="red">Vui lòng chuyển khoản đúng nội dung để hệ thống có thể kiểm tra nhanh nhất
                                ( &lt; 90 giây)</font>
						<br />
						<br />
						- Tỉ lệ nạp MoMo và ATM là 10:10 (10.000 VNĐ = 10.000 Coin).<br />
						- Coin nạp sử dụng trong > Mục <font color="red">"Chuyển xu"</font>.<br />

						- Nạp xong vui lòng chụp bill gửi vào FanPage để thông báo cho Admin add coin cho bạn.<br />
						- Nạp trên 1.000.000đ + 10%.<br />
						- Nạp trên 3.000.000đ + 15%.<br />
						- Nạp trên 5.000.000đ + 20%.<br />
                        </b>
                    </div>
                </div>

                <div id="napatm" class="tabcontent" style="display: none;">
                    <div class="card-body">
						<div class="card-body">
                            <img class="img-thumbnail" onclick="setTypecard(7)"
                                 src="/assets/img/pay/atm.png">
						<br>
                        Chuyển khoản ATM tới: {{$config['payment_bank_account']}}<br/>  ({{$config['payment_bank_vendor']}} )<br />
                        Nội dung: <b>{{Auth::guard('member')->user()->Email}}</b><br>
                        <br>
						<b>
                            <font color="blue">Vui lòng kích hoạt 2FA để bảo về tài khoản.</font>
						<br />
						<br />
                        <b>
                            <font color="red">Vui lòng chuyển khoản đúng nội dung để hệ thống có thể kiểm tra nhanh nhất
                                ( &lt; 90 giây)</font>
						<br />
						<br />
						- Tỉ lệ nạp MoMo và ATM là 10:10 (10.000 VNĐ = 10.000 Coin).<br />
						- Coin nạp sử dụng trong > Mục <font color="red">"Chuyển xu"</font>.<br />

						- Nạp xong vui lòng chụp bill gửi vào FanPage để thông báo cho Admin add coin cho bạn.<br />
						- Nạp trên 1.000.000đ + 10%.<br />
						- Nạp trên 3.000.000đ + 15%.<br />
						- Nạp trên 5.000.000đ + 20%.<br />
                        </b>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        var typeCard = 0;

        function setTypecard(type) {
            typeCard = type;
        };
    </script>
</section>
