<table class="table">
    <thead>
    <tr>
        <th scope="col">Type</th>
        <th scope="col">Value</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($values as $value)
        <tr>
            <th scope="row">{{ $value->type }}</th>
            <td>{{ $value->value }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
