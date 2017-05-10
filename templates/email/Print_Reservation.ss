<table class="print-reservation">

    <thead>
    <tr class="print-reservation__title">
        <th>
            <% if $Event.MailLogo %>
                <img src="$Event.MailLogo.Base64" alt="$Event.MailLogo.Title">
            <% else %>
                <h1><%t TicketEmail.Title 'Your tickets' %></h1>
            <% end_if %>
        </th>
    </tr>
    </thead>

    <tbody>

    <!-- scan code -->
    <tr>
        <td><% include Print_Event %></td>
        <%--td><% include Print_Event CurrentDate=$Event.Controller.CurrentDate %></td--%>
    </tr>

    <!-- attendee data -->
    <tr>
        <td><% include Print_Order %></td>
    </tr>

    <!-- text -->
    <tr>
        <td>
            <table class="text">
                <tbody>
                <tr>
                    <td class="text__col">$Event.MailContent</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    </tbody>
</table>
