<table class="event">
    <tbody>
    <tr>
        <td class="event__ticket-qr">
            <img src="$TicketQRCode.Base64" alt="$TicketQRCode.Title" width="448" height="448">
            <p class="event__ticket-code">$TicketCode</p>
        </td>

        <td class="event__info">
            <h2>$Event.Title</h2>
            <p class="event__location">$Event.Location</p>
            <% with $CurrentDate %>
                <p class="event__date">$DateRange,<% if $AllDay %> <%t CalendarDateTime.ALLDAY 'Allday' %><% else_if $StartTime  %> $TimeRange<% end_if %></p>
            <% end_with %>
        </td>
    </tr>
    </tbody>
</table>