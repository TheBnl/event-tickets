<table class="attendee">
<tbody>
<tr>
    <td rowspan="3" class="attendee__qr">
        <img src="$TicketQRCode.Base64" alt="$TicketQRCode.Title" width="256" height="256">
    </td>
    <td  class="attendee__ticket-title">
        $Ticket.Title
    </td>
    <td  class="attendee__ticket-price">
        $Ticket.Price.NiceDecimalPoint
    </td>
</tr>
<tr>
    <td colspan="2" class="attendee__name">
        $Title
    </td>
</tr>
<tr>
    <td colspan="2" class="attendee__links">
        <a href="{$BaseURL}{$ICSLink}" class="attendee__link attendee__link--agenda">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNSAxNiI+DQogIDxwYXRoIGQ9Ik0xNC44NiAzLjQzdjExLjQzYzAgLjYyLS41MiAxLjE0LTEuMTUgMS4xNEgxLjE1Qy41MiAxNiAwIDE1LjQ4IDAgMTQuODZWMy40M0MwIDIuOC41MiAyLjMgMS4xNCAyLjNIMi4zdi0uODdDMi4zLjYzIDIuOTIgMCAzLjcgMGguNThDNS4xIDAgNS43LjY0IDUuNyAxLjQzdi44NmgzLjQ0di0uODdDOS4xNC42MyA5LjggMCAxMC41NyAwaC41N2MuOCAwIDEuNDMuNjQgMS40MyAxLjQzdi44NmgxLjE0Yy42NCAwIDEuMTYuNSAxLjE2IDEuMTN6TTEzLjcgNS43SDEuMTV2OS4xNkgxMy43VjUuNzJ6TTMuNDQgNGMwIC4xNi4xMi4yOC4zLjI4aC41NmMuMTUgMCAuMjctLjEyLjI3LS4yOFYxLjQzYzAtLjE2LS4xMi0uMy0uMy0uM2gtLjU2Yy0uMTUgMC0uMjcuMTQtLjI3LjNWNHpNMTAgOS43Yy4xNiAwIC4zLjE0LjMuM3YuNTdjMCAuMTYtLjE0LjMtLjMuM0g4djJjMCAuMTUtLjEzLjI3LS4zLjI3aC0uNTZjLS4xNiAwLS4yOC0uMTItLjI4LS4yOHYtMmgtMmMtLjE2IDAtLjMtLjEzLS4zLS4zVjEwYzAtLjE2LjE0LS4zLjMtLjNoMlY3LjczYzAtLjE3LjEyLS4zLjI4LS4zaC41N2MuMTcgMCAuMy4xMy4zLjN2Mmgyem0uMy01LjdjMCAuMTYuMS4yOC4yNy4yOGguNTdjLjE2IDAgLjMtLjEyLjMtLjI4VjEuNDNjMC0uMTYtLjE0LS4zLS4zLS4zaC0uNTdjLS4xNiAwLS4yOC4xNC0uMjguM1Y0eiIvPg0KPC9zdmc+"
                 width="30" height="32"
                 alt="<%t TicketEmail.Agenda 'Agenda' %>"
                 class="attendee__link-icon">
            <span class="attendee__link-label"><%t TicketEmail.Agenda 'Agenda' %></span>
        </a>
        <a href="$TicketLink" class="attendee__link attendee__link--print">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNyAxNiIgb3ZlcmZsb3c9InZpc2libGUiPg0KICA8cGF0aCBkPSJNMTcgMTNjMCAuMTgtLjE1LjMzLS4zMy4zM0gxNC40VjE1YzAgLjU1LS40NSAxLTEgMUgzLjZjLS41NSAwLS45OC0uNDUtLjk4LTF2LTEuNjdILjMyYy0uMTcgMC0uMzItLjE1LS4zMi0uMzNWOC42N2MwLTEuMS45LTIgMS45Ni0yaC42NlYxYzAtLjU1LjQzLTEgLjk4LTFoNi44NmMuNTQgMCAxLjMuMyAxLjY4LjdsMS41NSAxLjZjLjM4LjQuNyAxLjE1LjcgMS43djIuNjdoLjY0YzEuMDcgMCAxLjk2LjkgMS45NiAyVjEzem0tMy45Mi01VjRoLTEuNjRjLS41NCAwLS45OC0uNDUtLjk4LTFWMS4zM0gzLjkyVjhoOS4xNnptMCA2LjY3VjEySDMuOTJ2Mi42N2g5LjE2ek0xNS4wNCA4Yy0uMzYgMC0uNjUuMy0uNjUuNjcgMCAuMzYuMjguNjYuNjQuNjZzLjY1LS4zLjY1LS42NmMwLS4zNy0uMy0uNjctLjY2LS42N3oiLz4NCjwvc3ZnPg=="
                 width="34" height="32"
                 alt="<%t TicketEmail.Print 'Print' %>"
                 class="attendee__link-icon">
            <span class="attendee__link-label"><%t TicketEmail.Print 'Print' %></span>
        </a>
        <a href="$Event.FacebookShareLink" class="attendee__link attendee__link--share">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5IDE2IiBvdmVyZmxvdz0idmlzaWJsZSI+DQogIDxwYXRoIGQ9Ik04LjMgMi42NUg2LjhjLTEuMiAwLTEuNC41Ny0xLjQgMS40djEuOGgyLjhMNy44NSA4LjdINS40VjE2SDIuNDRWOC43SDBWNS44NmgyLjQ1di0yLjFDMi40NSAxLjMzIDMuOTUgMCA2LjEyIDBjMS4wMyAwIDEuOTMuMDggMi4yLjF2Mi41NXoiLz4NCjwvc3ZnPg=="
                 width="18" height="32"
                 alt="<%t TicketEmail.Share 'Share' %>"
                 class="attendee__link-icon">
            <span class="attendee__link-label"><%t TicketEmail.Share 'Share' %></span>
        </a>
    </td>
</tr>
</tbody>
</table>