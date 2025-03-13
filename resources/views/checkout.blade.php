@extends('layouts.app')
@section('content')
<style>
  #row-paymentmode {
    display: -ms-flexbox;
    /* IE10 */
    display: flex;
    -ms-flex-wrap: wrap;
    /* IE10 */
    flex-wrap: wrap;
    margin: 0 -16px;
  }

  .col-25 {
    -ms-flex: 25%;
    /* IE10 */
    flex: 25%;
  }

  .col-50 {
    -ms-flex: 50%;
    /* IE10 */
    flex: 50%;
  }

  .col-75 {
    -ms-flex: 75%;
    /* IE10 */
    flex: 75%;
  }

  .col-25,
  .col-50,
  .col-75 {
    padding: 0 16px;
  }

  #container {
    background-color: #f2f2f2;
    padding: 5px 20px 15px 20px;
    border: 1px solid lightgrey;
    border-radius: 3px;
  }

  input[type=text] {
    width: 100%;
    margin-bottom: 20px;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 3px;
  }

  label {
    margin-bottom: 10px;
    display: block;
  }

  .icon-container {
    margin-bottom: 20px;
    padding: 7px 0;
    font-size: 24px;
  }

  .btn {
    background-color: #04AA6D;
    color: white;
    padding: 12px;
    margin: 10px 0;
    border: none;
    width: 100%;
    border-radius: 3px;
    cursor: pointer;
    font-size: 17px;
  }

  .btn:hover {
    background-color: #45a049;
  }

  span.price {
    float: right;
    color: grey;
  }

  /* Responsive layout - when the screen is less than 800px wide, make the two columns stack on top of each other instead of next to each other (and change the direction - make the "cart" column go on top) */
  @media (max-width: 800px) {
    .row {
      flex-direction: column-reverse;
    }

    .col-25 {
      margin-bottom: 20px;
    }
  }
</style>
<main class="pt-90">
  <div class="mb-4 pb-4"></div>
  <section class="shop-checkout container">
    <h2 class="page-title">Shipping and Checkout</h2>
    <div class="checkout-steps">
      <a href="{{route('cart.index')}}" class="checkout-steps__item active">
        <span class="checkout-steps__item-number">01</span>
        <span class="checkout-steps__item-title">
          <span>Shopping Bag</span>
          <em>Manage Your Items List</em>
        </span>
      </a>
      <a href="javascript:void(0)" class="checkout-steps__item active">
        <span class="checkout-steps__item-number">02</span>
        <span class="checkout-steps__item-title">
          <span>Shipping and Checkout</span>
          <em>Checkout Your Items List</em>
        </span>
      </a>
      <a href="javascript:void(0)" class="checkout-steps__item">
        <span class="checkout-steps__item-number">03</span>
        <span class="checkout-steps__item-title">
          <span>Confirmation</span>
          <em>Review And Submit Your Order</em>
        </span>
      </a>
    </div>
    <form name="checkout-form" action="{{route('cart.place.order')}}" method="post" id="payment-form">
      @csrf
      <div class="checkout-form">
        <div class="billing-info__wrapper">
          <div class="row">
            <div class="col-6">
              <h4>SHIPPING DETAILS</h4>
            </div>
            <div class="col-6">
            </div>
          </div>
          @if ($address)
          <div class="row">
            <div class="col-md-12">
              <div class="my-account__address-list">
                <div class="my-account__address-list-item">
                  <div class="my-account__address-list-item__details">
                    <p>{{$address->name}}</p>
                    <p>{{$address->address}}</p>
                    <p>{{$address->landmark}}</p>
                    <p>{{$address->city}}, {{$address->state}}, {{$address->country}}</p>
                    <p>{{$address->zip}}</p>
                    <br>
                    <p>{{$address->phone}}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @else
          <div class="row mt-5">
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="name" required="" value="{{old('name')}}">
                <label for="name">Full Name *</label>
                @error('name')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="phone" required="" value="{{old('phone')}}">
                <label for="phone">Phone Number *</label>
                @error('phone')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="zip" required="" value="{{old('zip')}}">
                <label for="zip">Pincode *</label>
                @error('zip')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating mt-3 mb-3">
                <input type="text" class="form-control" name="state" required="" value="{{old('state')}}">
                <label for="state">State *</label>
                @error('state')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="city" required="" value="{{old('city')}}">
                <label for="city">Town / City *</label>
                @error('city')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="address" required="" value="{{old('address')}}">
                <label for="address">House no, Building Name *</label>
                @error('address')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="locality" required="" value="{{old('locality')}}">
                <label for="locality">Road Name, Area, Colony *</label>
                @error('locality')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="landmark" required="" value="{{old('landmark')}}">
                <label for="landmark">Landmark *</label>
                @error('landmark')
                <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                @enderror
              </div>
            </div>
          </div>
          @endif
        </div>
        <div class="checkout__totals-wrapper">
          <div class="sticky-content">
            <div class="checkout__totals">
              <h3>Your Order</h3>
              <table class="checkout-cart-items">
                <thead>
                  <tr>
                    <th>PRODUCT</th>
                    <th align="right">SUBTOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach (Cart::instance('cart')->content() as $item)
                  <tr>
                    <td>
                      {{$item->name}} x {{$item->qty}}
                    </td>
                    <td align="right">
                      ${{$item->subtotal()}}
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
              @if (Session::has('discounts'))
              <table class="checkout-totals">
                <tbody>
                  <tr>
                    <th>SUBTOTAL</th>
                    <td align="right">${{Cart::instance('cart')->subtotal()}}</td>
                  </tr>
                  <tr>
                    <th>DISCOUNT {{Session::get('coupon')['code']}}</th>
                    <td align="right">${{Session::get('discounts')['discount']}}</td>
                  </tr>
                  <tr>
                    <th>SUBTOTAL AFTER DISCOUNT</th>
                    <td align="right">${{Session::get('discounts')['subtotal']}}</td>
                  </tr>
                  <tr>
                  <tr>
                    <th>SHIPPING</th>
                    <td align="right">Free shipping</td>
                  </tr>
                  <tr>
                    <th>VAT</th>
                    <td align="right">${{Session::get('discounts')['tax']}}</td>
                  </tr>
                  <tr>
                    <th>TOTAL</th>
                    <td align="right">${{Session::get('discounts')['total']}}</td>
                  </tr>
                </tbody>
                @else
                <table class="checkout-totals">
                  <tbody>
                    <tr>
                      <th>SUBTOTAL</th>
                      <td align="right">${{Cart::instance('cart')->subtotal()}}</td>
                    </tr>
                    <tr>
                      <th>SHIPPING</th>
                      <td align="right">Free shipping</td>
                    </tr>
                    <tr>
                      <th>VAT</th>
                      <td align="right">${{Cart::instance('cart')->tax()}}</td>
                    </tr>
                    <tr>
                      <th>TOTAL</th>
                      <td align="right">${{Cart::instance('cart')->total()}}</td>
                    </tr>
                  </tbody>
                  @endif
                </table>
            </div>
            <div class="checkout__payment-methods">
              <div class="form-check">
                <input class="form-check-input form-check-input_fill" type="radio" name="mode" id="mode1" value="card">
                <label class="form-check-label" for="mode1"> Debit Or Credit Card </label>
              </div>
              {{-- <div class="form-check">
                <input class="form-check-input form-check-input_fill" type="radio" name="mode" id="mode2"
                  value="paypal">
                <label class="form-check-label" for="mode2"> Paypal </label>
              </div> --}}
              <div class="form-check">
                <input class="form-check-input form-check-input_fill" type="radio" name="mode" id="mode3" value="cod"
                  checked>
                <label class="form-check-label" for="mode3"> Cash on delivery </label>
              </div>
              <div class="policy-text">
                Your personal data will be used to process your order, support your experience throughout this
                website, and for other purposes described in our <a href="terms.html" target="_blank">privacy
                  policy</a>.
              </div>
            </div>
            {{-- <div class="col-md-6"> --}}
              <div id="cc-info" style="display:none;">
                <h3>Credit Card Payment</h3>
                <div id="card-element">
                  <!-- Stripe's Card Element will go here -->
                  <div class="row" id="row-paymentmode">
                    <div class="col-75">
                      <div class="container" id="container">
                        <form action="/action_page.php">
                          <input type="hidden" name="order_id" value='12345'>

                          <div class="row">

                            <div class="col-50">
                              <h3>Payment</h3>
                              @if (session()->has('stripe_error'))
                              <span class="text-danger"
                                style="color:#f90505 !important;">{{session()->get('stripe_error')}}</span>
                              @endif
                              <label for="fname">Accepted Cards</label>
                              <div class="icon-container">
                                <i class="fa fa-cc-visa" style="color:navy;"></i>
                                <i class="fa fa-cc-amex" style="color:blue;"></i>
                                <i class="fa fa-cc-mastercard" style="color:red;"></i>
                                <i class="fa fa-cc-discover" style="color:orange;"></i>
                              </div>
                              <label for="cname">Name on Card</label>
                              <input type="text" id="cname" name="cardname" placeholder="John More Doe">
                              @error('cardname')
                              <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                              @enderror
                              <label for="ccnum">Credit card number</label>
                              <input type="text" id="ccnum" name="cardnumber" placeholder="1111-2222-3333-4444">
                              @error('cardnumber')
                              <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                              @enderror
                              <label for="expmonth">Exp Month</label>
                              <input type="text" id="expmonth" name="expmonth" placeholder="September">
                              @error('expmonth')
                              <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                              @enderror
                              <div class="row">
                                <div class="col-50">
                                  <label for="expyear">Exp Year</label>
                                  <input type="text" id="expyear" name="expyear" placeholder="2018">
                                  @error('expyear')
                                  <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                                  @enderror
                                </div>
                                <div class="col-50">
                                  <label for="cvc">CVC</label>
                                  <input type="text" id="cvc" name="cvc" placeholder="352">
                                  @error('cvc')
                                  <span class="text-danger" style="color:#f90505 !important;">{{$message}}</span>
                                  @enderror
                                </div>
                              </div>
                            </div>

                          </div>
                          <label>
                            <input type="checkbox" checked="checked" name="sameadr"> Shipping address same as billing
                          </label>
                          {{-- <input type="submit" value="Continue to checkout" class="btn"> --}}
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="card-errors" role="alert"></div>
                {{-- <button type="submit" id="submit-btn">Pay</button> --}}
              </div>

              <!-- Cash on Delivery Info (Visible by default) -->
              <div id="cod-info">
                <h3>Cash on Delivery</h3>
                <p>You selected Cash on Delivery.</p>
              </div>

              {{--
            </div> --}}
            <div class="row">
              <button class="btn btn-primary btn-checkout" type="submit" id="submit-btn">PLACE ORDER</button>
            </div>
          </div>
        </div>
    </form>
  </section>
</main>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>


<script>
  $(document).ready(function() {
        // Initially hide the credit card form and show the COD form
        $('#cc-info').hide();
        $('#cod-info').show();

        // Listen for change event on payment method radio buttons
        $('input[name="mode"]').on('change', function() {
            if ($(this).val() == 'card') {
                // If Credit Card is selected, show the credit card form and hide COD info
                $('#cc-info').show();
                $('#cod-info').hide();
            } else {
                // If Cash on Delivery is selected, show COD info and hide credit card form
                $('#cc-info').hide();
                $('#cod-info').show();
            }
        });
    });
         // Set up Stripe and Elements
         var stripe = Stripe('{{ config('services.stripe.key') }}');  // Your publishable key
        var elements = stripe.elements();

        // Create an instance of the card Element
        var card = elements.create('card');
        card.mount('#ccnum'); // Mount it to the DOM

        // Handle form submission
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Create a token from the card element
            stripe.createToken(card).then(function (result) {
                if (result.error) {
                    // Handle error (display error message to the user)
                    alert(result.error.message);
                } else {
                    // Add the token to the form and submit
                    var tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'stripeToken';
                    tokenInput.value = result.token.id;
                    form.appendChild(tokenInput);

                    form.submit(); // Now submit the form with the token
                }
            });
        });
</script>


@endpush
@endsection