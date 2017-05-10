<table class="order">
    <tbody>
    <tr>
        <td class="order__key"><%t TicketPrint.NAME 'Name' %></td>
        <td class="order__value">$Name</td>
        <td class="order__key"><%t TicketPrint.START 'Start' %></td>
        <td class="order__value">$Event.Controller.CurrentDate.StartTime.Nice24</td>
    </tr>
    <tr>
        <td class="order__key"><%t TicketPrint.TYPE 'Ticket type' %></td>
        <td class="order__value">$Ticket.Title</td>
        <td class="order__key"><%t TicketPrint.ORDER_NUMBER 'Order number' %></td>
        <td class="order__value">$Reservation.ReservationCode</td>
    </tr>
    <tr>
        <td class="order__key"><%t TicketPrint.PRICE 'Price' %></td>
        <td class="order__value">$Ticket.Price.NiceDecimalPoint</td>
        <td class="order__key">&nbsp;</td>
        <td class="order__value">&nbsp;</td>
    </tr>
    </tbody>
</table>