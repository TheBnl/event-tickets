<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
    <title>$TicketCode</title>
    <style type="text/css">
        html,
        body {
            width: 100%;
            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
            text-align: left;
        }

        table {
            width: 100%;
            border: none;
            padding: 0;
            margin: 0;
        }

        table th {
            text-align: left;
        }

        table tr {}
        table tr td {}

        .email-reservation {
            max-width: 650px;
            margin: auto;
            background-color: #ffffff;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.25);
        }

        .email-reservation__total td {
            line-height: 3rem;
            border-bottom: 1px solid #efefef;
        }

        .email-reservation__total-price {
            padding-right: 10px;
            font-weight: bold;
        }

        .email-reservation__content td {
            padding: 15px 0;
        }

        .attendee {
            background-color: #efefef;
            border-radius: 4px;
            margin: 15px 0;
        }

        .attendee__qr {
            width: 256px;
        }

        .attendee__ticket-title,
        .attendee__ticket-price {
            vertical-align: bottom;
        }

        .attendee__name {
            line-height: 1.2rem;
        }

        .attendee__links {
            vertical-align: bottom;
        }
        .attendee__link {
            margin-right: 10px;
            padding-bottom: 5px;
            font-weight: bold;
            color: #000000;
            text-decoration: none;
        }
        .attendee__link-icon {}
        .attendee__link-label {
            margin: 0 15px;
        }

        .attendee td {
            padding: 7px 20px;
        }

        .email-reservation__modifier-value,
        .email-reservation__total-price,
        .attendee__ticket-price {
            width: 300px;
            text-align: right;
        }
    </style>
</head>
<body>
    <% include Email_Reservation %>
</body>
</html>