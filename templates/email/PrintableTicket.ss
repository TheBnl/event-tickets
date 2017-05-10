<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
    <title>$TicketCode</title>
    <style type="text/css">
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
            font-size: 9pt;
            text-align: left;
            margin: .5cm;
        }

        table {
            width: 100%;
            border: none;
            padding: 0;
            margin: 0;
            border-spacing: 0;
        }

        table th {
            text-align: left;
        }

        table tr {}
        table tr td {
            line-height: 1.4;
            font-size: 1rem;
        }

        p {
            line-height: 1.4;
            font-size: 1rem;
        }

        .print-reservation {
            width: 100%;
            margin: auto;}
            .print-reservation__title img {
                width: 300px;
                margin: 1rem 0;}
            .print-reservation__logos {
                padding: 1rem;}
                .print-reservation__logos img {
                    float: left;
                    margin: 1rem .5rem;}

        .event {
            margin-bottom: 2rem;
            border: 2mm solid lightgrey;}
            .event__ticket-code {
                height: 0;
                line-height: 0;
                margin: 0;
                font-size: .6rem;
                position: relative;
                top: -0.5rem;
                width: 100%;
                text-align: center;}
            .event__ticket-qr {
                width: 256px;}
            .event__info {
                border-left: 2mm solid lightgrey;
                padding: 1rem;}
            .event__location {
                font-weight: bold;
                margin: 0;}
            .event__date {
                margin: 0;}

        .order {
            margin-bottom: 2rem;
            border: 2mm solid lightgrey;
            padding: 1rem;}
            .order__key {
                line-height: 1.4;}
            .order__value {
                padding: .1rem .5rem;
                line-height: 1.4;
                font-weight: bold;}

        .text {
            margin-bottom: 2rem;
            padding: 0 1rem;}
            .text__col {
                padding: 0 .5rem;
                vertical-align: top;}
            .text__col p, li {
                font-size: .8rem;}
    </style>
</head>
<body>
    <% include Print_Reservation %>
</body>
</html>