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

        body::-webkit-scrollbar {
            width: 5px;
            background-color: transparent;
        }

        body::-webkit-scrollbar-thumb {
            background-color: #254546;
        }

        pre {
            margin: 0px;
            margin-left: 30px;
        }

        .line-number {
            /*background-color: #d9ecd9;*/
            background-color: #1f2937;
            font-weight: 700;
            color: #b7b7b7;
            width: 10px;
            text-align: center;
            padding: 3px 7px 3px 7px;
            position: sticky;
            left: 0;
            font-size: .875rem;
        }

        .line-content {
            /* padding: 4px; */
            font-size: .875rem;
            line-height: 2;
        }

        .nav-link {
            font-size: 12px;
        }

        li.list-group-item:hover {
            background-color: #d9ecd9;
        }

        .line-err {
            /* background-color: #00800054 !important; */
            background-color: #ff849033 !important;
        }

        .list-group-item {
            word-break: break-word;
        }

        .nav-pills .nav-link {
            border-radius: 0px;
            word-break: break-all;
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            color: #426440;
            background-color: #254546;
            border-radius: 0px;
        }

        .nav-link:hover {
            color: #426440;
            border-radius: 0px;
            background-color: #254546;
        }

        .nav-link {
            color: #426440;
            padding: 13px;
            border-bottom: 1px solid #374154;
            display: flex;
            flex-direction: column;
        }

        .hljs {
            font-weight: 500;
            background: transparent;
        }

        pre code.hljs {
            font-weight: 400;
            color: #fff;
            padding: 0em;
        }

        code.hljs {
            font-weight: 500;
            padding: 0px;
        }

        code,
        kbd,
        pre,
        samp {
            font-family: 'Fira Code', monospace;
            font-size: 1em;
        }

        ._line:hover {
            background-color: #254546 !important;
            cursor: default;
        }

        ._line:hover>.line-number {
            background-color: #183738 !important;
        }

        .content-scrolls::-webkit-scrollbar {
            width: 1px;
            background-color: transparent;
        }

        .hljs-comment,
        .hljs-quote {
            font-weight: 500;
            color: rgb(156 163 175);
        }

        .hljs-attr,
        .hljs-deletion,
        .hljs-function.hljs-keyword,
        .hljs-literal,
        .hljs-section,
        .hljs-selector-tag {
            font-weight: 500;
            color: rgb(139 92 246);
        }

        .hljs-bullet,
        .hljs-link,
        .hljs-meta,
        .hljs-operator,
        .hljs-selector-id,
        .hljs-symbol,
        .hljs-title,
        .hljs-variable {
            font-weight: 500;
            color: rgb(129 140 248);
        }

        .hljs-addition,
        .hljs-attribute,
        .hljs-meta-string,
        .hljs-regexp,
        .hljs-string {
            font-weight: 500;
            color: rgb(96 165 250);
        }

        .hljs-doctag,
        .hljs-formula,
        .hljs-keyword,
        .hljs-name {
            font-weight: 500;
            color: rgb(248 113 113);
        }

        .hljs-built_in,
        .hljs-class .hljs-title,
        .hljs-template-tag,
        .hljs-template-variable {
            font-weight: 500;
            color: rgb(249 115 22);
        }

        .hljs-number,
        .hljs-selector-attr,
        .hljs-selector-class,
        .hljs-selector-pseudo,
        .hljs-string.hljs-subst,
        .hljs-type {
            font-weight: 500;
            color: rgb(52 211 153);
        }

        .hljs-doctag,
        .hljs-formula,
        .hljs-keyword,
        .hljs-name {
            font-weight: 500;
            color: rgb(248 113 113);
        }

        .hljs-attr,
        .hljs-deletion,
        .hljs-function.hljs-keyword,
        .hljs-literal,
        .hljs-section,
        .hljs-selector-tag {
            color: rgb(139 92 246);
        }
    </style>
    <script src='{{jquery}}'></script>
    <script src='{{popper}}'></script>
    <script src='{{bstrap}}'></script>
</head>

<body style="color: #fff;background-color: #111827;">
    <div class='container-fluid' style="width: 89%;">

        <div class='row justify-content-md-center'>
            <div class='col-md-12'>
                <div class='card' style='margin-top: 3%;background-color: #1F2937;/* border: 2px solid #e1dfdf; *//* border-radius: 3px; */color: #fff;padding: 10px;'>
                    <div class='card-body d-flex flex-column' style='padding: 27px;'>
                        <p class='text-muted' style='margin: 0px;font-size: 16px;font-weight: 400;background-color: #0000001f;padding: 4px 10px;width: fit-content;/* color: #fff; */'>{{$exceptionClass}}</p>
                        <p class='mt-2' style='font-size: 25px;font-weight: 500;'>{{$message}}</p>
                        <small class='text-muted' style='font-size: 14px;font-weight: 700;'>{{$r_uri}}</small>

                    </div>
                </div>
            </div>
        </div>

        <div class='row justify-content-md-center'>
            <div class='col-md-12'>
                <div class='card' style='margin-top: 2%;margin-bottom: 5%;background-color: #1F2937;'>
                    <div class='d-flex flex-row justify-content-between card-header' style='padding: 15px;background-color: transparent;color: #d5d5d5;font-weight: 500;'>
                        <div>
                            Sprnva Blast : Stack Trace
                        </div>
                        <div>
                            <small style='color:#d5d5d5;font-size: 14px;font-weight: 500;'>{{$app_version}}</small>
                        </div>
                    </div>
                    <div class='card-body d-flex flex-column' style="padding-top: 0px;padding-bottom: 0px;padding-left: 14px;padding-right: 14px;">

                        <div class='row'>
                            <div class='col-md-4' style='padding-top: 15px;padding: 0px;border-right: 1px solid #374154;'>
                                <div class="nav flex-column nav-pills mb-3" id="v-pills-tab" role="tablist" aria-orientation="vertical" style='overflow: hidden;overflow-x: auto;overflow-y: auto;padding: 0px;height: 100%;'>
                                    {{$traceContent}}
                                </div>
                            </div>
                            <div class='col-md-8' style='padding: 0px;padding-top: 15px;'>
                                <div class='col-md-12' style='background-color: #1f2937;color: #fff;overflow: hidden;padding: 0px;height: 100%;'>
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