<table class="email-reservation">
    <thead>
    <tr class="email-reservation__title">
        <th colspan="3">
            <% if $Logo %>
                <img src="$Logo.Base64" alt="$Logo.Title">
            <% else %>
                <h1><%t TicketEmail.Title 'Your tickets' %></h1>
            <% end_if %>
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
                <% include Email_Attendee ICSLink=$Top.CurrentDate.ICSLink, TicketLink=$Top.TicketFile.AbsoluteLink %>
            <% end_loop %>
        </td>
    </tr>

        <% if $PriceModifiers %>
            <% loop $PriceModifiers %>
            <tr class="email-reservation__modifier">
                <td class="email-reservation__modifier-title" colspan="2">$TableTitle</td>
                <td class="email-reservation__modifier-value">$TableValue</td>
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
    <tr class="email-reservation__content">
        <td colspan="3">
            $Event.MailContent
        </td>
    </tr>
    </tbody>
</table>