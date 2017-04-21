<table class="email-reservation">
    <thead>
    <tr class="email-reservation__title">
        <th colspan="3">
            <h1><%t TicketEmail.Notification 'New ticket order {order}' order=$ReservationCode %></h1>
        </th>
    </tr>
    <tr class="email-reservation__event-summary">
        <th colspan="3">
            <h2>$Event.Title</h2>
            <% with $CurrentDate %>
                <p>$DateRange<% if $AllDay %> <%t CalendarDateTime.ALLDAY 'Allday' %><% else_if $StartTime %> $TimeRange<% end_if %></p>
            <% end_with %>
            <p>$Event.Location</p>
        </th>
    </tr>
    </thead>
    <tbody>
    <tr class="email-reservation__attendees">
        <td colspan="3">
            <% loop $Attendees %>
                <% include Email_NotificationAttendee %>
            <% end_loop %>
        </td>
    </tr>

        <% if $PriceModifiers %>
            <% loop $PriceModifiers %>
            <tr class="email-reservation__modifier">
                <td class="email-reservation__modifier-title" colspan="2">$TableTitle</td>
                <td class="email-reservation__modifier-value">$TableValue.NiceDecimalPoint</td>
            </tr>
            <% end_loop %>
        <% end_if %>

    <tr class="email-reservation__total">
        <td colspan="2">
            <%t TicketEmail.Total 'Total' %>
        </td>
        <td class="email-reservation__total-price">
            $Total.NiceDecimalPoint
        </td>
    </tr>
    </tbody>
</table>