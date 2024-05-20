<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel</title>
    <link rel="stylesheet" href="/css/app.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-1 col-sm-1"></div>
            <form method="post" action="/input" class="card py-3 my-5 col-lg-6 col-md-10" enctype="multipart/form-data">
                @csrf
                <input type="file" accept=".mp3" class="form-control" id="fileInput" name="fileInput" accept=".mp3">
                <div id="audio">

                </div>
            </form>
            <div class="col-lg-3 col-md-1 col-sm-1"></div>
        </div>
    </div>

    <script src="/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/2.0.4/wavesurfer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/3.2.0/plugin/wavesurfer.timeline.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.5/jsmediatags.min.js"></script>

    <script>
        $(document).ready(function () {
            var wavesurfer = null;
            $('#fileInput').on('change', function () {
                if (wavesurfer !== null) {
                    wavesurfer.destroy();
                }
                $('#audio').html('');
                const file = this.files[0];
                if (file) {
                    $('#audio').append(`
                        <h3 class="mt-4">Display</h3>
                        <input type="range" id="zoom-slider" min="1" max="9" step="1" value="1" class="mt-2">
                        <div id="wave-timeline"></div>
                        <div id="waveform"></div>
                        <div class="row mt-3">
                            <div class="col-4"><div id="current-time" class="mt-2">Current Time: 00:00</div></div>
                            <div class="col-4 text-center"><button type="button" id="play" class="btn btn-outline-success btn-sm">Play/Stop</button></div>
                            <div class="col-4"><div id="duration" class="mt-2 text-right">Total Duration: 00:00</div></div>
                        </div>

                        <h3 class="mt-4">Convert</h3>
                        <div class="row mt-3">
                            <div class="col-6">
                                <select id="type_format" class="form-control">
                                    <option value="wav">wav</option>
                                    <option value="aac">aac</option>
                                </select>    
                            </div>
                            <div class="col-6"><button type="button" id="convert" class="btn btn-success btn-sm">Convert</button></div>
                        </div>
                    `);

                    // khởi tạo wave
                    wavesurfer = WaveSurfer.create({
                        container: '#waveform',
                        waveColor: 'violet',
                        progressColor: 'purple',
                        plugins: [
                            WaveSurfer.timeline.create({
                                container: '#wave-timeline'
                            })
                        ]
                    });

                    // đọc file và hiển thị wave
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        wavesurfer.load(event.target.result);
                    };
                    reader.readAsDataURL(file);

                    // hiển thị thời lượng của file
                    wavesurfer.on('ready', function () {
                        let duration = wavesurfer.getDuration();
                        let minutes = Math.floor(duration / 60);
                        let seconds = Math.floor(duration % 60);
                        $('#duration').text(`Total Duration: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
                    });

                    // click chạy audio
                    $('#play').on('click', function () {
                        wavesurfer.playPause();
                    });

                    // hiển thị thời gian chạy
                    wavesurfer.on('audioprocess', function () {
                        let currentTime = wavesurfer.getCurrentTime();
                        let minutes = Math.floor(currentTime / 60);
                        let seconds = Math.floor(currentTime % 60);
                        $('#current-time').text(`Current Time: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
                    });

                    // zoom audio
                    var last_zoom = 1;
                    $('#zoom-slider').on('input', function () {
                        if ($(this).val() > last_zoom) {
                            wavesurfer.zoom(Number($(this).val()) * 10);
                        } else {
                            wavesurfer.zoom(Number($(this).val()));
                        }
                        last_zoom = $(this).val();
                    });

                    // Đọc metadata từ file MP3
                    jsmediatags.read(file, {
                        onSuccess: function (tag) {
                            console.log(tag);
                            // displayMetadata(tag);
                        },
                        onError: function (error) {
                            console.log(':(', error.type, error.info);
                        }
                    });

                    // chuyển đổi định dạng
                    $('#convert').on('click', function () {
                        let sourceAudioFile = file;
                        let targetAudioFormat = $('#type_format').val();
                        
                    });
                }
            });
        });

    </script>
</body>

</html>