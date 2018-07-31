<form action="{{route('add-lot')}}" method="post">
    {{ csrf_field() }}
    <label for="currency_id">currency_id</label>
    <input type="text" name='currency_id' id="currency_id" value="">

    <label for="seller_id">seller_id</label>
    <input type="text" name='seller_id' id="seller_id" value="">

    <label for="date_time_open">date_time_open</label>
    <input type="text" name='date_time_open' id='date_time_open' value="">

    <label for="date_time_close">date_time_close</label>
    <input type="text" name='date_time_close' id="date_time_close" value="">

    <label for="price">price</label>
    <input type="text" name='price' id="price" value="">

    <input type="submit" value="Send">
</form>