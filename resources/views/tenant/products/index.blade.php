<h1>Available Products/Services</h1>
<form method="GET" action="{{ route('tenant.products.index') }}">
    <input type="text" name="search" placeholder="Search by name">
    <select name="category">
        <option value="">All Categories</option>
        <!-- List categories dynamically if needed -->
    </select>
    <button type="submit">Search</button>
</form>

@foreach ($products as $product)
    <div>
        <h2>{{ $product->name }}</h2>
        <p>{{ $product->description }}</p>
        <p>{{ $product->price }}</p>
        <p>{{ $product->category }}</p>
        <p>{{ $product->type }}</p>
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
        <form action="{{ route('tenant.products.order', $product->id) }}" method="POST">
            @csrf
            <button type="submit">Order</button>
        </form>
    </div>
@endforeach

{{ $products->links() }}
