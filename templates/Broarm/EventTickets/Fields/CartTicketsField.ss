<table class="tickets-table" border="0">
    <thead class="tickets-table__header">
        <tr class="tickets-table__header-row">
            <th class="tickets-table__header-col"><%t Broarm\EventTickets\Fields\TicketsField.TICKETS 'Tickets' %></th>
            <th class="tickets-table__header-col"><%t Broarm\EventTickets\Fields\TicketsField.PRICE 'Price' %></th>
            <th class="tickets-table__header-col"><%t Broarm\EventTickets\Fields\TicketsField.AMOUNT 'Amount' %></th>
        </tr>
    </thead>
    <tbody class="tickets-table__body tickets-table__body--tickets">
    <% loop $Tickets %>
        <tr class="tickets-table__body<% if $Available %> tickets-table__body--available<% else %> tickets-table__body--unavailable<% end_if %>">
            <td class="tickets-table__body-col tickets-table__body--title">
                <% with $Buyable %>
                    <h5><a href="$TicketPage.Link">$TicketPage.Title</a></h5>
                    <p>$Title</p>
                <% end_with %>
            </td>
            <td class="tickets-table__body-col tickets-table__body--price">$Price.Nice</td>
            <td class="tickets-table__body-col tickets-table__body--amount">$AmountField</td>
        </tr>
    <% end_loop %>
    </tbody>
</table>