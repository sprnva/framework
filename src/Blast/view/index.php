<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='icon' href='{{icon}}' type='image/ico'>
    <title>{{$title}}</title>

    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel='stylesheet' href='{{custFont}}'>
    <link rel='stylesheet' href='{{hjscss}}'>
    <script src='{{hjsscript}}'></script>
    <link rel='stylesheet' href='{{css}}'>
    <style>
        body {
            font-family: 'Fira Code', monospace;
            background-color: #eef1f4;
            font-weight: 700;
            color: #2c6727;
        }

        pre {
            margin: 0px;
            margin-left: 30px;
        }

        .line-number {
            /*background-color: #d9ecd9;*/
            background-color: #fff;
            font-weight: 700;
            color: #7a7a7a;
            text-align: center;
            padding: 3px 7px 3px 7px;
            position: sticky;
            left: 0;
            font-size: 12px;
        }

        .line-content {
            padding: 4px;
            font-size: 1em;
        }

        .nav-link {
            font-size: 12px;
        }

        li.list-group-item:hover {
            background-color: #d9ecd9;
        }

        .line-err {
            /* background-color: #00800054 !important; */
            background-color: #dc354530 !important;
        }

        .list-group-item {
            word-break: break-word;
        }

        .nav-pills .nav-link {
            word-break: break-all;
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            color: #426440;
            background-color: #d9ecd9;
            border-radius: 0px;
        }

        .nav-link:hover {
            color: #426440;
            border-radius: 0px;
            background-color: #d9ecd9;
        }

        .nav-link {
            color: #426440;
            padding: 13px;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .hljs {
            background: transparent;
            color: #444;
        }

        pre code.hljs {
            padding: 0em;
        }

        code.hljs {
            font-weight: 700;
            padding: 0px;
        }

        code,
        kbd,
        pre,
        samp {
            font-family: 'Fira Code', monospace;
        }

        ._line:hover {
            background-color: #00800054 !important;
            cursor: default;
        }

        ._line:hover>.line-number {
            background-color: #73b973 !important;
        }
    </style>
    <script src='{{jquery}}'></script>
    <script src='{{popper}}'></script>
    <script src='{{bstrap}}'></script>
</head>
<div class='container'>

    <div class='row justify-content-md-center'>
        <div class='col-md-12'>
            <div class='card' style='margin-top: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px; padding: 10px;'>
                <div class='card-body d-flex flex-column' style='padding: 50px;'>
                    <p class='text-muted' style='margin: 0px;font-size: 18px;''>{{$exceptionClass}}</p>
                    <p class='' style=' font-size: 26px;'>{{$message}}</p>
                    <small class='text-muted' style='font-size: 14px;font-weight: 700;'>{{$r_uri}}</small>

                </div>
            </div>
        </div>
    </div>

    <div class='row justify-content-md-center'>
        <div class='col-md-12'>
            <div class='card' style='margin-top: 2%;margin-bottom: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px;'>
                <div class='d-flex flex-row justify-content-between card-header' style='padding: 15px;background-color: #1e4d1a;color: #fff;'>
                    <div>
                        Sprnva Blast : Stack Trace
                    </div>
                    <div>
                        <small style='font-size: 14px;font-weight: 700;'>{{$app_version}}</small>

                    </div>
                </div>
                <div class='card-body d-flex flex-column' style="padding-top: 0px;padding-bottom: 0px;padding-left: 14px;padding-right: 14px;">

                    <div class='row'>
                        <div class='col-md-4' style='padding-top: 15px;padding: 0px;border-right: 1px solid #ccc;'>
                            <div class="nav flex-column nav-pills mb-3" id="v-pills-tab" role="tablist" aria-orientation="vertical" style='overflow: hidden;overflow-x: auto;overflow-y: auto;padding: 0px;height: 100%;'>
                                {{$traceContent}}
                            </div>
                        </div>
                        <div class='col-md-8' style='padding: 0px;padding-top: 15px;'>
                            <div class='col-md-12' style='background-color: #fff;overflow: hidden;padding: 0px;height: 100%;'>
                                <div class="tab-content" id="v-pills-tabContent">
                                    {{$fileContent}}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
<script>
    hljs.highlightAll();
</script>

</html>