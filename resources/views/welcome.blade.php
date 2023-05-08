<!DOCTYPE html>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .center {
            margin: auto;
            width: 60%;
            padding: 10px;
            height: 100vh;
        }

        .qr-code {
            width: 100%;
        }
    </style>

    <title>ePrint</title>
</head>

<body onload="JavaScript:setTimeout(timedCheck,10000);">
<div class="center">
        <div class="text-center" id="qr">
            <img class="qr-code" />
        </div>
        <div class="text-center py-5" id="spinner" style="display: none">
            <div class="py-5">
                <div class="spinner-border" role="status">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>
        <div class="text-center" id="label">

        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.js">
    </script>

    <script>
        function htmlEncode(value) {
            return $('<div/>').text(value)
                .html();
        }
        function uuidv4() {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            );
        }


        $(document).ready(function () {
            const baseUrl = "{{$url}}"
            let sessionId = uuidv4()
            let price
            let loaded = false
            let countdown = false
            let m = 1
            let s = 59
            let date = false
            const finalURL = 'https://chart.googleapis.com/chart?cht=qr&chl=' +
                htmlEncode(baseUrl + '/' + sessionId) +
                '&chs=500x500&chld=L|0'
            $('.qr-code').attr('src', finalURL);
            $("#link").attr("href", baseUrl + '/' + sessionId)

            $.get('index.php/api/' + sessionId + '/init').then((result) => {
                result = JSON.parse(result)
                if(result.success == true)
                {
                    sessionCheck()
                }
                else
                {
                    $("#qr").hide()
                    $("#label").html("<h1>Error</h1>");
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                }
                
            })
            const delay = (delayInms) => {
                return new Promise(resolve => setTimeout(resolve, delayInms));
            }
            var startTimer = setInterval(function () {
                var now = new Date().getTime();
                var distance = date - now;
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                $("#countdown").html(`0${minutes}:${seconds < 10 ? "0" + seconds : seconds}`)
                if (distance < 0 && date != false) {

                    clearInterval(startTimer);
                }
            }, 1000);
            const sessionCheck = async () => {
                while (true) {
                    try {
                        await $.get('index.php/api/' + sessionId).then((result) => {
                            if (result.status == 1) {
                                $("#qr").hide()
                                $("#spinner").show()
                                $("#label").html("<h1>Please upload file</h1>");
                            }
                            else if (result.status == 2 && loaded == false) {
                                $("#qr").hide()
                                $("#label").html("<h1>Please Choose print option</h1>");
                            }
                            else if (result.status == 3) {

                                if (date == false) {
                                    date = new Date(result.time).getTime();
                                }
                                $("#qr").hide()
                                $("#spinner").show()
                                $("#label").html("<h1>Please Pay</h1>");
                            }
                            else if (result.status == 4) {
                                $("#paymentQr").hide()
                                $("#spinner").show()
                                $("#qr").hide()
                                $("#price").hide()
                                $("#paymentQr").hide()
                                $("#label").show();
                                $("#label").html("<h1>Printing</h1>");
                            }

                            else if (result.status == 6) {
                                location.reload();
                            }
                        });
                    } catch (error) {
                    }
                }
            }
        });
        function timedCheck(timeoutPeriod) {
            if(checkJSNetConnection()==false){
            }else{
                location.reload(true);
            }
        }
    </script>
</body>

</html>