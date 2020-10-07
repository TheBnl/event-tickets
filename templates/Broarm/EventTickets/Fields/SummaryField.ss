<table class="summary-table" border="0">
    <thead class="summary-table__header">
    <tr>
        <th class="summary-table__header-col summary-table__header-col--ticket"><%t Broarm\EventTickets\Fields\SummaryField.Ticket 'Ticket' %></th>
        <% with $Attendees.First %>
            <% loop $TableFields %>
                <td class="summary-table__header-col summary-table__header-col--{$Header}">$Header</td>
            <% end_loop %>
        <% end_with %>
        <th class="summary-table__header-col summary-table__header-col--price"><%t Broarm\EventTickets\Fields\SummaryField.Price 'Price' %></th>
    </tr>
    </thead>
    <tbody class="summary-table__body">
        <% loop $Attendees %>
        <tr>
            <td class="summary-table__body-col summary-table__body-col--ticket">$Ticket.Title</td>
            <% loop $TableFields %>
                <td class="summary-table__body-col summary-table__body-col--{$Header}">$Value</td>
            <% end_loop %>
            <td class="summary-table__body-col summary-table__body-col--price">$Ticket.Price.NiceDecimalPoint</td>
        </tr>
        <% end_loop %>
    </tbody>
    <tfoot class="summary-table__footer">
        <tr class="summary-table__footer-row summary-table__footer-row--sub-total">
            <td class="summary-table__footer-col summary-table__footer-col--total-label" colspan="{$Attendees.First.TableFields.Count}"><%t Broarm\EventTickets\Fields\SummaryField.Subtotal 'Subtotal' %></td>
            <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
            <td class="summary-table__footer-col summary-table__footer-col--total-value">$Reservation.Subtotal.NiceDecimalPoint</td>
        </tr>
        <% if $Reservation.PriceModifiers %>
            <% loop $Reservation.PriceModifiers %>
            <tr class="summary-table__footer-row summary-table__footer-row--modifier">
                <td class="summary-table__footer-col summary-table__footer-col--modifier-title" colspan="{$Top.Attendees.First.TableFields.Count}">$TableTitle</td>
                <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
                <td class="summary-table__footer-col summary-table__footer-col--modifier-value">$TableValue.NiceDecimalPoint</td>
            </tr>
            <% end_loop %>
        <% end_if %>
        <tr class="summary-table__footer-row summary-table__footer-row--total">
            <td class="summary-table__footer-col summary-table__footer-col--total-label" colspan="{$Attendees.First.TableFields.Count}"><%t Broarm\EventTickets\Fields\SummaryField.Total 'Total' %></td>
            <td class="summary-table__footer-col summary-table__footer-col--spacer">&nbsp;</td>
            <td class="summary-table__footer-col summary-table__footer-col--total-value">$Reservation.Total.NiceDecimalPoint</td>
        </tr>
    </tfoot>
</table>
