<div class="grid-container">
    <div class="grid-x grid-padding-x">
        <div class="cell large-8">
            <h1 class="h2">$Title</h1>
            $Content
            $CartForm

            <% if $SiteConfig.ContinueShoppingPage %>
                <a href="$SiteConfig.ContinueShoppingPage.Link" class="button"><%t Broarm\EventTickets\Forms\CartForm.ContinueShopping 'Continue shopping' %></a>
            <% end_if %>
            <a href="$Reservation.CheckoutLink" class="button"><%t Broarm\EventTickets\Forms\CartForm.Checkout 'Checkout' %></a>
        </div>
    </div>
</div>
