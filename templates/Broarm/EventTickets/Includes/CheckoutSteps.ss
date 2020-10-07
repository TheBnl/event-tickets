<ul class="checkout-steps">
    <% loop $CheckoutSteps %>
        <% if $InPast %>
        <li class="checkout-steps__step checkout-steps__step--past checkout-steps__step--level-{$Pos}">
        <% else_if $InFuture %>
        <li class="checkout-steps__step checkout-steps__step--future checkout-steps__step--level-{$Pos}">
        <% else %>
        <li class="checkout-steps__step checkout-steps__step--current checkout-steps__step--level-{$Pos}">
        <% end_if %>
        <% if $InPast %>
            <a href="$Link">$Title</a>
        <% else %>
            <span>$Title</span>
        <% end_if %>
    <% end_loop %>
</ul>
