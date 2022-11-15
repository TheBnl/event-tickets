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
            <td class="tickets-table__body-col tickets-table__body--title">$Title</td>
            <td class="tickets-table__body-col tickets-table__body--price">$Price.Nice</td>
            <td class="tickets-table__body-col tickets-table__body--amount">$AmountField</td>
        </tr>
    <% end_loop %>
    </tbody>
    <%-- i $Note %><!-- TODO: add ability to add notes, like amount of tickets left, etc -->
    <tfoot class="tickets-table__footer">
        <tr class="tickets-table__footer-row">
            <td class="tickets-table__footer-col" colspan="3">
                <small>$Note</small>
            </td>
        </tr>
    </tfoot>
    <% end_if --%>
</table>