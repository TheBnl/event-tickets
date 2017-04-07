<table class="summary-table" border="0">
    <thead class="summary-table__header">
    <tr>
        <% if $Editable %>
            <th class="summary-table__header-col summary-table__header-col--email-receiver"><%t SummaryField.EMAIL_RECEIVER 'Email receiver' %></th>
        <% end_if %>

        <th class="summary-table__header-col summary-table__header-col--ticket"><%t SummaryField.TICKET 'Ticket' %></th>
        <% with $Attendees.First %>
            <% loop $TableFields %>
                <td class="summary-table__header-col summary-table__header-col--{$Header}">$Header</td>
            <% end_loop %>
        <% end_with %>
        <th class="summary-table__header-col summary-table__header-col--price"><%t SummaryField.PRICE 'Price' %></th>
    </tr>
    </thead>
    <tbody class="summary-table__body">
        <% loop $Attendees %>
        <tr>
            <% if $Top.Editable %>
            <td class="summary-table__body-col summary-table__body-col--email-receiver">$TicketReceiverField</td>
            <% end_if %>
            <td class="summary-table__body-col summary-table__body-col--ticket">$Ticket.Title</td>
            <% loop $TableFields %>
                <td class="summary-table__body-col summary-table__body-col--{$Header}">$Value</td>
            <% end_loop %>
            <td class="summary-table__body-col summary-table__body-col--price">$Ticket.PriceNice</td>
        </tr>
        <% end_loop %>
    </tbody>
    <tfoot class="summary-table__footer">
        <% if $Reservation.PriceModifiers %>
            <% loop $Reservation.PriceModifiers %>
            <tr class="summary-table__footer-row summary-table__footer-row--modifier">
                <td class="summary-table__footer-col summary-table__footer-col--modifier-title" colspan="{$Top.Attendees.First.TableFields.Count}">$TableTitle</td>
                <td class="summary-table__footer-col summary-table__footer-col--spacer"<% if $Top.Editable %> colspan="2"<% end_if %>>&nbsp;</td>
                <td class="summary-table__footer-col summary-table__footer-col--modifier-value">$TableValue.NiceDecimalPoint</td>
            </tr>
            <% end_loop %>
        <% end_if %>
    <tr class="summary-table__footer-row summary-table__footer-row--total">
        <td class="summary-table__footer-col summary-table__footer-col--total-label" colspan="{$Attendees.First.TableFields.Count}"><%t SummaryField.TOTAL 'Total' %></td>
        <td class="summary-table__footer-col summary-table__footer-col--spacer"<% if $Editable %> colspan="2"<% end_if %>>&nbsp;</td>
        <td class="summary-table__footer-col summary-table__footer-col--total-value">$Reservation.Total.NiceDecimalPoint</td>
    </tr>
    </tfoot>
</table>