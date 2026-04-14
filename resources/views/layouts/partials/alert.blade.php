@if (session('success'))
<script type="application/json" id="session-success-data">@json(session('success'))</script>
@endif