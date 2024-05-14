<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <link rel="stylesheet" href="/css/app.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-1 col-sm-1"></div>
            <div class="card py-3 my-5 col-lg-6 col-md-10">
                <input type="file" accept=".mp3" class="form-control" id="fileInput" accept=".mp3">
                <div id="audio">
                    <div id="waveform"></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-1 col-sm-1"></div>
        </div>
    </div>

    <script src="/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/2.0.4/wavesurfer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/3.2.0/plugin/wavesurfer.timeline.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#fileInput').on('change', function () {
                const file = this.files[0];
                if (file) {
                    $('#audio').prepend(`
                        <input type="range" id="zoom-slider" min="1" max="10" step="1" value="1" class="mt-4">
                    `)
                    $('#audio').append(`
                        <div id="wave-timeline"></div>
                        <div class="row mt-3">
                            <div class="col-4"><div id="current-time" class="mt-2">Current Time: 00:00</div></div>
                            <div class="col-4 text-center"><button id="play">Play/Stop</button></div>
                            <div class="col-4"><div id="duration" class="mt-2 text-right">Total Duration: 00:00</div></div>
                        </div>
                    `);

                    // khởi tạo wave
                    let wavesurfer = WaveSurfer.create({
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

                        // log metadata
                        console.log(wavesurfer.backend.getPeaks(1000));
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
                    $('#zoom-slider').on('change', function () {
                        wavesurfer.zoom(Number($(this).val()) * 10);
                    });
                }
            });
        });
    </script>
</body>

</html>