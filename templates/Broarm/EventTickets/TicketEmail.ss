<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><%t Broarm\EventTickets\Model\Reservation.ReservationSubject 'Your tickets for {event}' event=$TicketPage.EventTitle %></title>
    <style type="text/css">
        * {
            font-family: akzidenz-grotesk-next, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            border: 0;
        }
        table,tr,th,td {
            vertical-align: baseline;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse; border-spacing: 0; width: 100%; margin-bottom: 30px;
        }
        th {
            font-size: 16px; font-weight: 600; padding: 0 10px 6px 0; white-space: nowrap;
        }
        td {
            font-size: 16px; border-collapse: collapse; padding: 0 10px 6px 0;
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
            font-size: 16px;
        }
        .payoff a {
            text-decoration: none; color: black;
        }
    </style>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage">

    <% if $TicketPage.MailContent %>
        $TicketPage.MailContent
    <% end_if %>

    <p class="payoff">
        <% with $SiteConfig %>
            <strong>$Title</strong><br/>
            <% if $Suburb %>$Suburb<br/><% end_if %>
            <% if $Phone %><a href="tel:$Phone">$Phone</a><br/><% end_if %>
            <a href="$AbsoluteBaseURL">$AbsoluteBaseURL</a><br/>
            <% if $SocialMedia %>
                <% loop $SocialMedia %>
                    <a href="$Link">$Title</a><br/>
                <% end_loop %>
            <% end_if %>
        <% end_with %>
    </p>

</body>
</html>
