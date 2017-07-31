<div class="row">
    <div class="large-12 columns">
        <h1><%t CheckIn.CHECK_IN_TITLE 'Check in guests in for {event}' event=$Title %></h1>
        $CheckInForm

        <h2><%t CheckIn.GUESTLIST 'Guest list' %></h2>
        <table class="guest-list-table" border="0">
            <thead class="guest-list-table__header">
            <tr>
                <th class="guest-list-table__header-col"><%t CheckIn.NUMBER_HEADER '#' %></th>
                <th class="guest-list-table__header-col"><%t CheckIn.NAME_HEADER 'Name' %></th>
                <th class="guest-list-table__header-col"><%t CheckIn.TICKET_HEADER 'Ticket' %></th>
                <th class="guest-list-table__header-col"><%t CheckIn.TICKET_NUMBER_HEADER 'Ticket number' %></th>
                <th class="guest-list-table__header-col" colspan="2"><%t CheckIn.CHECKED_IN_HEADER 'Checked in' %> $CheckedInCount</th>
            </tr>
            </thead>
            <tbody class="guest-list-table__body">
                <% loop $GuestList %>
                <tr>
                    <td class="guest-list-table__body-col">$Pos</td>
                    <td class="guest-list-table__body-col">$Name</td>
                    <td class="guest-list-table__body-col">$Ticket.Title</td>
                    <td class="guest-list-table__body-col">$TicketCode</td>
                    <td class="guest-list-table__body-col">$CheckedIn.Nice</td>
                    <td class="guest-list-table__body-col">
                        <a href="$CheckInLink" class="small button">
                            <% if $CheckedIn && $canCheckOut %>
                                <%t CheckInForm.CheckOut 'Check out' %>
                            <% else %>
                                <%t CheckInForm.CheckIn 'Check in' %>
                            <% end_if %>
                        </a>
                    </td>
                </tr>
                <% end_loop %>
            </tbody>
        </table>
    </div>
</div>