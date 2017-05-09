<% if $TicketForm %>
    $TicketForm
<% else_if $WaitingListSuccess %>
    <p><%t TicketForm.WAITINGLIST_SUCCESS 'Thank you for your registration, we will keep you informed as soon as there is a place available.' %></p>
<% else %>
    <p><%t TicketForm.WAITINGLIST_INTRO 'The event is sold out. You can sign up below to be informed when places are released.' %></p>
    $WaitingListRegistrationForm
<% end_if %>