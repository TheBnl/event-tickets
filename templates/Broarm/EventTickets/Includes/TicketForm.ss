<% if $WaitingListSuccess %>
    <p><%t Broarm\EventTickets\Forms\TicketForm.WAITINGLIST_SUCCESS 'Thank you for your registration, we will keep you informed as soon as there is a place available.' %></p>
<% else_if $WaitingListRegistrationForm %>
    <p><%t Broarm\EventTickets\Forms\TicketForm.WAITINGLIST_INTRO 'The event is sold out. You can sign up below to be informed when places are released.' %></p>
    $WaitingListRegistrationForm
<% else_if $TicketSalePending %>
    <p><%t Broarm\EventTickets\Forms\TicketForm.PENDING 'Ticket sale starts from {date}.' date=$TicketSaleStartDate.Nice %></p>
<% else_if $EventExpired %>
    <p><%t Broarm\EventTickets\Forms\TicketForm.EXPIRED 'The event is expired.' %></p>
<% else_if $TicketForm %>
    $TicketForm
<% end_if %>
