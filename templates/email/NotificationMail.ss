<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><%t NotificationMail.TITLE 'New order for {event}' event=$Event.Title %></title>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;height: 100%;font-size: 14px;line-height: 1.6em;background-color: #f6f6f6;width: 100% !important;">

<table class="body-wrap" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;background-color: #f6f6f6;width: 100%;">
    <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
        <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;"></td>
        <td class="container" width="600" style="margin: 0 auto !important;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;display: block !important;max-width: 600px !important;clear: both !important;">
            <div class="content" style="margin: 0 auto;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;max-width: 600px;display: block;padding: 20px;">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;background-color: #fff;border: 1px solid #e9e9e9;border-radius: 3px;">
                    <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                        <td class="content-wrap aligncenter" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 20px;text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                    <td class="content-block title-block" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 0 0 20px;text-align: center;">
                                        <% if $Event.MailLogo %>
                                            <a href="$BaseURL" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;color: #348eda;text-decoration: underline;">
                                                <img src="$Event.MailLogo.SetWidth(256).AbsoluteLink" alt="$Event.MailLogo.Title" width="256" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;max-width: 100%;">
                                            </a>
                                        <% else %>
                                            <h1 style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif;box-sizing: border-box;color: #000;line-height: 1.2em;font-weight: 500;font-size: 32px;"><a href="$BaseURL" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;color: #348eda;text-decoration: underline;"><%t NotificationMail.TITLE 'New order for {event}' event=$Event.Title %></a></h1>
                                        <% end_if %>
                                    </td>
                                </tr>
                                <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                    <td class="content-block lead-block" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 20px;">
                                        <h2 class="aligncenter" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif;box-sizing: border-box;color: #000;line-height: 1.2em;font-weight: 400;font-size: 24px;text-align: center;"><%t NotificationMail.CONTENT 'A order is placed by {name} for {event}. Below you can find the order details.' name=$MainContact.Name event=$Event.Title %></h2>
                                    </td>
                                </tr>
                                <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                    <td class="content-block aligncenter" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 0 0 20px;text-align: center;">
                                        <table class="invoice" style="margin: 40px auto;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;text-align: left;width: 80%;">
                                            <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;">
                                                    $MainContact.Name<br style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                    <%t NotificationMail.ORDER 'Order <strong>#{code}</strong>' code=$ReservationCode %><br style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                    $Created.FormatI18N('%e %b %Y')
                                                </td>
                                            </tr>
                                            <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;">
                                                    <table class="invoice-items" cellpadding="0" cellspacing="0" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;width: 100%;">
                                                        <% loop $Attendees %>
                                                            <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                                <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;border-top: #eee 1px solid;">$Name</td>
                                                                <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;border-top: #eee 1px solid;">$Ticket.Title</td>
                                                                <td class="alignright" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;text-align: right;padding: 5px 0;border-top: #eee 1px solid;">$Ticket.Price.NiceDecimalPoint</td>
                                                            </tr>
                                                        <% end_loop %>
                                                        <tr class="total" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                            <td width="80%" colspan="2" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;border-top: 2px solid #333;border-bottom: 2px solid #333;font-weight: 700;"><%t SummaryField.SUBTOTAL 'Subtotal' %></td>
                                                            <td class="alignright" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;text-align: right;padding: 5px 0;border-top: 2px solid #333;border-bottom: 2px solid #333;font-weight: 700;">$Subtotal.NiceDecimalPoint</td>
                                                        </tr>
                                                        <% loop $PriceModifiers %>
                                                        <% if $TableValue != 0 %>
                                                            <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                                <td colspan="2" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;border-top: #eee 1px solid;">$TableTitle</td>
                                                                <td class="alignright" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;text-align: right;padding: 5px 0;border-top: #eee 1px solid;">$TableValue.NiceDecimalPoint</td>
                                                            </tr>
                                                        <% end_if %>
                                                        <% end_loop %>
                                                        <tr class="total" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                            <td width="80%" colspan="2" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;border-top: 2px solid #333;border-bottom: 2px solid #333;font-weight: 700;"><%t SummaryField.TOTAL 'Total' %></td>
                                                            <td class="alignright" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;text-align: right;padding: 5px 0;border-top: 2px solid #333;border-bottom: 2px solid #333;font-weight: 700;">$Total.NiceDecimalPoint</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <% if $Comments %>
                                                <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                                    <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 5px 0;">
                                                        <p style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;margin-bottom: 10px;font-weight: normal;"><strong style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;"><%t SummaryForm.COMMENTS 'Comments' %></strong></p>
                                                        $Comments
                                                    </td>
                                                </tr>
                                            <% end_if %>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                                    <td class="content-block aligncenter" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;padding: 0 0 20px;text-align: center;">
                                        <% with $SiteConfig %>
                                            <strong style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;"><a href="$BaseURL" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;color: #348eda;text-decoration: underline;">{$Title}</a></strong>. $Address, $Postcode $Suburb
                                        <% end_with %>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="footer" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;width: 100%;clear: both;color: #999;padding: 20px;">
                    <table width="100%" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                        <tr style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;">
                            <td class="aligncenter content-block" style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 12px;padding: 0 0 20px;text-align: center;color: #999;">
                                <%t TicketEmail.FOOTER 'Questions? Email <a href="mailto:{email}">{email}</a> or call <a href="tel:{phone}">{phone}</a>' email=$SiteConfig.Email phone=$SiteConfig.Phone %>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
        <td style="margin: 0;font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;box-sizing: border-box;vertical-align: top;font-size: 14px;"></td>
    </tr>
</table>

</body>
</html>