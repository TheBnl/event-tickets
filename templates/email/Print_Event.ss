<table class="event">
    <tbody>
    <tr>
        <td class="event__ticket-qr">
            <img src="$TicketQRCode.Base64" alt="$TicketQRCode.Title" width="256" height="256">
            <p class="event__ticket-code">$TicketCode</p>
        </td>

        <td class="event__info">
            <h2>$Event.Title</h2>
            <p class="event__location">$Event.Location<% if $Event.Suburb %>, $Event.Suburb<% end_if %></p>
            <% with $Event.Controller.CurrentDate %>
                <p class="event__date">$DateRange,<% if $AllDay %> <%t CalendarDateTime.ALLDAY 'Allday' %><% else_if $StartTime  %> $TimeRange<% end_if %></p>
            <% end_with %>
        </td>
    </tr>
    </tbody>
</table>