<table class="summary-table" border="0">
    <thead class="summary-table__header">
    <tr>
        <th class="summary-table__header-col summary-table__header-col--ticket"><%t Broarm\EventTickets\Fields\SummaryField.Ticket 'Ticket' %></th>
        <th class="summary-table__header-col summary-table__header-col--amount"><%t Broarm\EventTickets\Fields\SummaryField.Amount 'Amount' %></th>
        <th class="summary-table__header-col summary-table__header-col--price"><%t Broarm\EventTickets\Fields\SummaryField.Price 'Price' %></th>
    </tr>
    </thead>
    <tbody class="summary-table__body">
        <% loop $Reservation.OrderItems %>
        <tr>
            <td class="summary-table__body-col summary-table__body-col--ticket">$Buyable.Title</td>
            <td class="summary-table__body-col summary-table__body-col--amount">$Amount</td>
            <td class="summary-table__body-col summary-table__body-col--price">$Total.Nice</td>
        </tr>
        <% end_loop %>
    </tbody>
    <tfoot class="summary-table__footer">
        <tr class="summary-table__footer-row summary-table__footer-row--sub-total">
            <td class="summary-table__footer-col summary-table__footer-col--total-label"><%t Broarm\EventTickets\Fields\SummaryField.Subtotal 'Subtotal' %></td>
            <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
            <td class="summary-table__footer-col summary-table__footer-col--total-value">$Reservation.Subtotal.Nice</td>
        </tr>
        <% if $Reservation.PriceModifiers %>
            <% loop $Reservation.PriceModifiers %>
            <tr class="summary-table__footer-row summary-table__footer-row--modifier">
                <td class="summary-table__footer-col summary-table__footer-col--modifier-title">$TableTitle</td>
                <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
                <td class="summary-table__footer-col summary-table__footer-col--modifier-value">$TableValue.Nice</td>
            </tr>
            <% end_loop %>
        <% end_if %>
        <tr class="summary-table__footer-row summary-table__footer-row--total">
            <td class="summary-table__footer-col summary-table__footer-col--total-label"><%t Broarm\EventTickets\Fields\SummaryField.Total 'Total' %></td>
            <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
            <td class="summary-table__footer-col summary-table__footer-col--total-value">$Reservation.Total.Nice</td>
        </tr>
    </tfoot>
</table>
