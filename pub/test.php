<!doctype html>
<html>
<head>
<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>

<script>
$(function() {
    $('input[type=submit]').click(function(e) {
        e.preventDefault();
        $('textarea#response').val('');
        $.ajax({
            type: $('input[type=radio]:checked').val(),
            url: 'http://localhost' + $('input[type=text]').val(),
            data: $('textarea#request').val(),
            dataType: 'json',
            success: function (response) {
                $('textarea#response').val(JSON.stringify(response));
            },
            error: function (response) {
                $('textarea#response').val(response.responseText);
            }
        });
    });
});
</script>
</head>
<body>


<form>

<fieldset>
<div>
[ <input type="radio" name="method" value="GET" checked/> GET ]
[ <input type="radio" name="method" value="POST"/> POST]
[ <input type="radio" name="method" value="PUT"/> PUT ]
[ <input type="radio" name="method" value="DELETE"/> DELETE ]
</div>

<div>
<input type="text" value="/hello-world"/>
</div>

<div>
<textarea id="request" rows="30" cols="90">
{}
</textarea>

<textarea id="response"rows="30" cols="90"></textarea>
</div>

</fieldset>

<input type="submit"/>
</form>

</body>
</html>

