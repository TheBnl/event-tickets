<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>$TicketCode</title>
    <style type="text/css">
        * {
            font-family: akzidenz-grotesk-next, 'Helvetica Neue', Arial, sans-serif;
            font-size: 9pt;
            border: 0;
        }
        html,
        body {
            font-family: akzidenz-grotesk-next, 'Helvetica Neue', Arial, sans-serif;
            font-size: 9pt;
            text-align: left;
            margin: 0;
        }
        p {
            margin: 0 0 1rem 0;
        }
        table,tr,th,td {
            vertical-align: top;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse; border-spacing: 0; width: 100%; margin-bottom: 30px;
        }
        th {
            font-size: 9pt; font-weight: 600; padding: 0 10px 6px 0; white-space: nowrap;
        }
        td {
            font-size: 9pt; border-collapse: collapse; padding: 0 10px 6px 0;
        }
        .align-left {
            text-align: left
        }
        .align-right {
            text-align: right;
        }
        .divider td {
            border-top: 1px solid black;
            padding: 6px 10px 6px 0;
        }
        .payoff {
            font-size: 9pt;
        }
        .payoff a {
            text-decoration: none; color: black;
        }

        .page {
            margin: 0;
        }

        .page--break {
            page-break-after: always;
        }

        .page__logo {
            margin-bottom: 1cm;
        }

        .attendee {
        }

        .attendee__qr-code {
            width: 6cm;
            text-align: center;
        }

        .attendee__data {
            margin: 1.5cm 0 1cm;
        }

        .ticket-content {
            width: 13cm;
        }

    </style>
</head>
<body>
<% loop $Attendees %>
    <div class="page<% if not $Last %> page--break<% end_if %>">
        <img class="page__logo" src="$SiteConfig.TicketLogo.ScaleWidth(160).AbsoluteURL" alt="$SiteConfig.Title" width="160">
        <table class="attendee">
            <tr>
                <td class="attendee__qr-code" style="border: 1px solid black; border-right: 0; padding-bottom: .5cm;">
                    <img src="data:image/png;base64, $QRCode" alt="$TicketCode" width="224" height="224">
                    <p>
                        <strong>$TicketCode</strong><br/>
                        $Name
                    </p>
                </td>
                <td style="border: 1px solid black; border-left: 0;">
                    <table class="attendee__data">
                        <tr>
                            <td><%t Broarm\EventTickets\Model\Reservation.Event 'Event' %></td>
                            <td>$TicketPage.EventTitle</td>
                        </tr>
                        <tr>
                            <td><%t Broarm\EventTickets\Model\Reservation.Address 'Address' %></td>
                            <td>$TicketPage.EventAddress</td>
                        </tr>
                        <tr>
                            <td><%t Broarm\EventTickets\Model\Reservation.Date 'Date' %></td>
                            <td>$TicketPage.EventStartDate.Nice</td>
                        </tr>
                        <tr>
                            <td><%t Broarm\EventTickets\Model\Reservation.Ticket 'Ticket' %></td>
                            <td>$Ticket.Title</td>
                        </tr>
                        <tr>
                            <td><%t Broarm\EventTickets\Model\Reservation.Price 'Price' %></td>
                            <td>$Ticket.Price.NiceDecimalPoint.RAW</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <% if $TicketPage.TicketContent %>
            <div class="ticket-content">
            $TicketPage.TicketContent.RAW
            </div>
        <% end_if %>
    </div>
<% end_loop %>
</body>
</html>
