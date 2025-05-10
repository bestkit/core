<!doctype html>
<html @if ($direction) dir="{{ $direction }}" @endif
      @if ($language) lang="{{ $language }}" @endif>
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>

        {!! $head !!}
    </head>

    <body>
        {!! $layout !!}

        <div id="modal"></div>
        <div id="alerts"></div>

        <script>
            document.getElementById('bestkit-loading').style.display = 'block';
            var bestkit = {extensions: {}};
        </script>

        {!! $js !!}

        <script id="bestkit-json-payload" type="application/json">@json($payload)</script>

        <script>
            const data = JSON.parse(document.getElementById('bestkit-json-payload').textContent);
            document.getElementById('bestkit-loading').style.display = 'none';

            try {
                bestkit.core.app.load(data);
                bestkit.core.app.bootExtensions(bestkit.extensions);
                bestkit.core.app.boot();
            } catch (e) {
                var error = document.getElementById('bestkit-loading-error');
                error.innerHTML += document.getElementById('bestkit-content').textContent;
                error.style.display = 'block';
                throw e;
            }
        </script>

        {!! $foot !!}
    </body>
</html>
