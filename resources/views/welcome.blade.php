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
            <form method="post" id="post_form" class="card py-3 my-5 col-lg-6 col-md-10" enctype="multipart/form-data">
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
        $(document).ready(() => {
            var wavesurfer = null;
            $('#fileInput').on('change', (e) => {
                if (wavesurfer !== null) {
                    wavesurfer.destroy();
                }
                $('#audio').html('');
                const file = e.target.files[0];
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

                        <h3 class="mt-4">Extract Part</h3>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="start_time">Start time</label>
                                    <input type="text" class="form-control time_input" id="start_time" pattern="^([0-5][0-9]):([0-5][0-9])$" placeholder="mm:ss">
                                    <span class="text-danger"></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="end_time">End time</label>
                                <input type="text" class="form-control time_input" id="end_time" pattern="^([0-5][0-9]):([0-5][0-9])$" placeholder="mm:ss">
                                <span class="text-danger"></span>
                            </div>
                            <div class="col-4 d-flex align-items-center">
                                <button type="button" class="btn btn-warning btn-sm" id="extract">Extract Part</button>
                            </div>
                        </div>

                        <h3 class="mt-4">Convert</h3>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Target format</label>
                                    <select name="type_format" class="form-control">
                                        <option value="wav">wav</option>
                                        <option value="aac">aac</option>
                                        <option value="flac">flac</option>
                                        <option value="alac">alac</option>
                                        <option value="ogg">ogg</option>
                                        <option value="wma">wma</option>
                                    </select> 
                                </div>
                            </div>
                            <div class="col-6 d-flex align-items-center"><button type="button" id="convert" class="btn btn-success btn-sm">Convert</button></div>
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
                    reader.onload = (event) => {
                        wavesurfer.load(event.target.result);
                    };
                    reader.readAsDataURL(file);

                    // hiển thị thời lượng của file
                    wavesurfer.on('ready', () => {
                        let duration = wavesurfer.getDuration();
                        let minutes = Math.floor(duration / 60);
                        let seconds = Math.floor(duration % 60);
                        $('#duration').text(`Total Duration: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
                    });

                    // click chạy audio
                    $('#play').on('click', () => {
                        wavesurfer.playPause();
                    });

                    // hiển thị thời gian chạy
                    wavesurfer.on('audioprocess', () => {
                        let currentTime = wavesurfer.getCurrentTime();
                        let minutes = Math.floor(currentTime / 60);
                        let seconds = Math.floor(currentTime % 60);
                        $('#current-time').text(`Current Time: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
                    });

                    // zoom audio
                    var last_zoom = 1;
                    $('#zoom-slider').on('input', () => {
                        if ($(this).val() > last_zoom) {
                            wavesurfer.zoom(Number($(this).val()) * 10);
                        } else {
                            wavesurfer.zoom(Number($(this).val()));
                        }
                        last_zoom = $(this).val();
                    });

                    // Đọc metadata từ file MP3
                    jsmediatags.read(file, {
                        onSuccess: (tag) => {
                            displayMetadata(tag);
                        },
                        onError: (error) => {
                            console.log(':(', error.type, error.info);
                        }
                    });

                    // chuyển đổi định dạng
                    $('#convert').on('click', () => {
                        $('#post_form').attr('action', '/convert').submit();
                    });

                    // check dữ liệu nhập thời gian
                    $('.time_input').on('input', function() {
                        let value = $(this).val();
                        if (!/^([0-5][0-9]):([0-5][0-9])$/.test(value)) {
                            $(this).closest('.col-4').find('span').html('Vui lòng nhập đúng định dạng mm:ss');
                        }else{
                            $(this).closest('.col-4').find('span').html('');
                        }
                    })

                    // trích đoạn audio
                    $('#extract').on('click', () => {
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        if(!start_time || !end_time){
                            alert('Vui lòng nhập khoảng thời gian để thực hiện trích xuất');
                        }else{
                            let total_start = (parseInt((start_time.split(':'))[0], 10) * 60) + parseInt((start_time.split(':'))[1], 10);
                            let total_end = (parseInt((end_time.split(':'))[0], 10) * 60) + parseInt((end_time.split(':'))[1], 10);
                            var max_time = wavesurfer.getDuration();
                            if(total_end > max_time){
                                alert('Không hợp lệ - Tổng thời gian trích xuất đã lớn hơn thời lượng file!');
                            }else if(total_start > total_end){
                                alert('Không hợp lệ - Thời gian bắt đầu trích xuất không thể lớn hơn thời gian kết thúc trích xuất!');
                            }else{
                                $('#post_form').attr('action', `/extract/${total_start}/${(total_end-total_start)}`).submit();
                            }
                        }
                    });
                }
            });
        });

        displayMetadata = (tag) => {
            $('#audio').append(`
                <h3 class="mt-4">MetaData</h3>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label for="">album</label>
                            <input type="text" class="form-control" id="album" name="album">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label for="">artist</label>
                            <input type="text" class="form-control" id="artist" name="artist">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label for="">genre</label>
                            <input type="text" class="form-control" id="genre" name="genre">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label for="">title</label>
                            <input type="text" class="form-control" id="title" name="title">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="">image</label>
                            <input type="file" accept=".png, .jpg, .jpeg" class="form-control" id="image" name="image">
                            <div class="mt-1" style="display: flex; justify-content: center;">
                                <img src="" id="picture" style="height:200px; width:200px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-info" type="button" id="save_mtdt">Lưu Metadata</button>
                        </div>
                    </div>
                </div>
            `);
            $('#album').val(tag.tags.album);
            $('#artist').val(tag.tags.artist);
            $('#genre').val(tag.tags.genre);
            $('#title').val(tag.tags.title);
            const base64String = arrayBufferToBase64(tag.tags.picture.data);
            const imageUrl = `data:${picture.format};base64,${base64String}`;
            $('#picture').attr('src', imageUrl);

            $('#image').on('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        $('#picture').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#save_mtdt').on('click', ()=>{
                $('#post_form').attr('action', '/input').submit();
            })
        }

        arrayBufferToBase64 = (buffer) => {
            let binary = '';
            const bytes = new Uint8Array(buffer);
            const len = bytes.byteLength;
            for (let i = 0; i < len; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return window.btoa(binary);
        }
    </script>
</body>

</html>