<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function store_brand(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:jpg,png,jpeg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateBrandThumbnailsImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Added Successfully!');
    }

    public function edit_brand($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function update_brand(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:jpg,png,jpeg|max:2048',
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->hasFile('image'))
        {
            if (File::exists(public_path('/uploads/brands') . '/' . $brand->image))
            {
                File::delete(public_path('/uploads/brands') . '/' . $brand->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailsImage($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Updated Successfully!');
    }

    public function delete_brand($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('/uploads/brands') . '/' . $brand->image))
        {
            File::delete(public_path('/uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Deleted Successfully!');
    }

    public function GenerateBrandThumbnailsImage($image, $image_name)
    {
        $destination_path = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint)
        {
            $constraint->aspectRatio();
        })->save($destination_path . '/' . $image_name);
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function add_category()
    {
        return view('admin.category-add');
    }

    public function store_category(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoryThumbnailsImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category Has Been Added Successfully!');
    }

    public function edit_category($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function update_category(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:jpg,png,jpeg|max:2048',
        ]);
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        if ($request->hasFile('image'))
        {
            if (File::exists(public_path('/uploads/categories') . '/' . $category->image))
            {
                File::delete(public_path('/uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateCategoryThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category Has Been Updated Successfully!');
    }

    public function delete_category($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('/uploads/categories') . '/' . $category->image))
        {
            File::delete(public_path('/uploads/categories') . '/' . $category->image);
        };
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category Has Been Deleted Successfully!');
    }

    public function GenerateCategoryThumbnailsImage($image, $image_name)
    {
        $destination_path = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint)
        {
            $constraint->aspectRatio();
        })->save($destination_path . '/' . $image_name);
    }

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function add_product()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }

    public function store_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:4096',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image'))
        {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images'))
        {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach ($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedExtensions);
                if ($gcheck)
                {
                    $gfileName = $current_timestamp . "." . $counter . $gextension;
                    $this->GenerateProductThumbnailsImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product Has Been Added Successfully!');
    }

    public function GenerateProductThumbnailsImage($image, $image_name)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint)
        {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $image_name);

        $img->resize(104, 104, function ($constraint)
        {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail . '/' . $image_name);
    }

    public function edit_product($id)
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $product = Product::find($id);
        return view('admin.product-edit', compact('categories', 'brands', 'product'));
    }

    public function update_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:4096',
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/products/' . $product->image)))
            {
                File::delete(public_path('uploads/products/' . $product->image));
            }
            if (File::exists(public_path('uploads/products/thumbnails/' . $product->image)))
            {
                File::delete(public_path('uploads/products/thumbnails/' . $product->image));
            }
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images'))
        {
            foreach ($request->file('images') as $file)
            {
                if (File::exists(public_path('uploads/products/' . $file)))
                {
                    File::delete(public_path('uploads/products/' . $file));
                }
                if (File::exists(public_path('uploads/products/thumbnails/' . $file)))
                {
                    File::delete(public_path('uploads/products/thumbnails/' . $file));
                }
            }
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach ($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedExtensions);
                if ($gcheck)
                {
                    $gfileName = $current_timestamp . "." . $counter . $gextension;
                    $this->GenerateProductThumbnailsImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
            $product->images = $gallery_images;
        }
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product Has Been Updated Successfully!');
    }

    public function delete($id)
    {
        dd($id);
        $product = Product::find($id);
        if (File::exists(public_path('uploads/products/' . $product->image)))
        {
            File::delete(public_path('uploads/products/' . $product->image));
        }
        if (File::exists(public_path('uploads/products/thumbnails/' . $product->image)))
        {
            File::delete(public_path('uploads/products/thumbnails/' . $product->image));
        }
        foreach (explode(',', $product->images) as $file)
        {
            if (File::exists(public_path('uploads/products/' . $file)))
            {
                File::delete(public_path('uploads/products/' . $file));
            }
            if (File::exists(public_path('uploads/products/thumbnails/' . $file)))
            {
                File::delete(public_path('uploads/products/thumbnails/' . $file));
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product Has Been Deleted Successfully!');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'desc')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function add_coupon()
    {
        return view('admin.coupon-add');
    }

    public function store_coupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);
        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon Has Been Added Successfully!');
    }

    public function edit_coupon($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function update_coupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);
        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status', 'Coupon Has Been Updated Successfully!');
    }

    public function delete_coupon($id)
    {
        Coupon::find($id)->delete();
        return redirect()->route('admin.coupons')->with('status', 'Coupon Has Been Deleted Successfully!');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.orders', compact('orders'));
    }

    public function show_order($id)
    {
        $order = Order::find($id);
        $orderItems = OrderItem::where('order_id', $id)->orderBy('id')->paginate(12);
        return view('admin.order-details', compact('order', 'orderItems'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->id);
        $order->status = $request->status;
        if ($request->status == 'delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        else if ($request->status == 'canceled')
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if ($request->status == 'delivered')
        {
            $transaction = Transaction::where('order_id', $request->id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with('status', 'Order Status Has Been Updated Successfully!');
    }

    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(10);
        return view('admin.slides', compact('slides'));
    }

    public function add_slide()
    {
        return view('admin.slide-add');
    }

    public function store_slide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:4096',
            'status' => 'required',
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->status = $request->status;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide Has Been Added Successfully!');
    }

    public function GenerateSlideThumbnailsImage($image, $image_name)
    {
        $destination_path = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690, 'top');
        $img->resize(400, 690, function ($constraint)
        {
            $constraint->aspectRatio();
        })->save($destination_path . '/' . $image_name);
    }

    public function edit_slide($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function update_slide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:4096',
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image'))
        {
            if (File::exists(public_path('/uploads/slides') . '/' . $slide->image))
            {
                File::delete(public_path('/uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateSlideThumbnailsImage($image, $file_name);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide Has Been Updated Successfully!');
    }

    public function delete_slide($id)
    {
        $slide = Slide::find($id);
        if (File::exists(public_path('/uploads/slides') . '/' . $slide->image))
        {
            File::delete(public_path('/uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide Has Been Deleted Successfully!');
    }
}
