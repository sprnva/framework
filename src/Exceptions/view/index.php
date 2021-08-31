<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='icon' href='{{icon}}' type='image/ico'>
    <title>ERROR</title>
    <link rel='stylesheet' href='{{css}}'>
    <style>
        body {
            background-color: #eef1f4;
            color: #2c6727;
        }

        pre {
            margin: 0px;
            margin-left: 30px;
        }

        .line-number {
            min-width: 60px;
            background-color: #d9ecd9;
            text-align: center;
            padding: 3px;
            position: sticky;
            left: 0;
        }

        .line-content {}

        li.list-group-item:hover {
            background-color: #d9ecd9;
        }

        .line-err {
            background-color: #00800054 !important;
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
                    <p class='' style=' font-size: 30px;font-weight: 500;'>{{$message}}</p>
                    <small class='text-muted' style='font-size: 14px;'>{{$r_uri}}</small>
                </div>
            </div>
        </div>
    </div>

    <div class='row justify-content-md-center'>
        <div class='col-md-12'>
            <div class='card' style='margin-top: 2%;margin-bottom: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px;'>
                <div class='card-header' style='padding: 15px;background-color: #1e4d1a;color: #fff;'>
                    Sprnva Blast : Stack Trace
                </div>
                <div class='card-body d-flex flex-column' style="padding-top: 0px;padding-bottom: 0px;padding-left: 14px;padding-right: 14px;">

                    <div class='row'>
                        <div class='col-md-4' style='padding-top: 15px;padding: 0px;border-right: 1px solid #ccc;'>
                            <div class="nav flex-column nav-pills mb-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                {{$traceContent}}
                            </div>
                        </div>
                        <div class='col-md-8' style='padding: 0px;padding-top: 15px;'>
                            <div class='col-md-12' style='background-color: #fff;overflow: hidden;overflow-x: auto;overflow-y: auto;padding: 0px;'>
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

</html>