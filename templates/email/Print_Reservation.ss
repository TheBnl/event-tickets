<% loop $Attendees %>
    <!-- page break -->
    <table class="print-reservation">

        <thead>
        <tr class="print-reservation__title">
            <th>
                <% if $Top.Logo %>
                    <img src="$Top.Logo.Base64" alt="$Top.Logo.Title">
                <% else %>
                    <h1><%t TicketEmail.Title 'Your tickets' %></h1>
                <% end_if %>
            </th>
        </tr>
        </thead>

        <tbody>

        <!-- scan code -->
        <tr>
            <td><% include Print_Event CurrentDate=$Top.CurrentDate %></td>
        </tr>

        <!-- attendee data -->
        <tr>
            <td><% include Print_Order CurrentDate=$Top.CurrentDate %></td>
        </tr>

        <!-- text -->
        <tr>
            <td>$Event.MailContent</td>
        </tr>

        </tbody>
    </table>
<% end_loop %>