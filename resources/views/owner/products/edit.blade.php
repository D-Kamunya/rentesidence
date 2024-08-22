<h1>Edit Product/Service</h1>
<form action="{{ route('owner.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="text" name="name" value="{{ $product->name }}" placeholder="Name" required>
    <textarea name="description" placeholder="Description">{{ $product->description }}</textarea>
    <input type="number" name="price" value="{{ $product->price }}" placeholder="Price" required>
    <input type="text" name="category" value="{{ $product->category }}" placeholder="Category" required>
    <select name="type" required>
        <option value="product" {{ $product->type == 'product' ? 'selected' : '' }}>Product</option>
        <option value="service" {{ $product->type == 'service' ? 'selected' : '' }}>Service</option>
    </select>
    <input type="file" name="image">
    <button type="submit">Update</button>
</form>
