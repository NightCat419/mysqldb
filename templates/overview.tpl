
{foreach from=$productcustomfields item=customfield}
    <div class="row">
        <div class="col-sm-5">
            {$customfield.name}
        </div>
        <div class="col-sm-7">
            {$customfield.value}
        </div>
    </div>
{/foreach}

{if $lastupdate}
    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareadiskusage}
        </div>
        <div class="col-sm-7">
            {$diskusage}MB / {$disklimit}MB ({$diskpercent})
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareabwusage}
        </div>
        <div class="col-sm-7">
            {$bwusage}MB / {$bwlimit}MB ({$bwpercent})
        </div>
    </div>
{/if}

<div class="row">
    <div class="col-sm-5">
        {$LANG.orderpaymentmethod}
    </div>
    <div class="col-sm-7">
        {$paymentmethod}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.firstpaymentamount}
    </div>
    <div class="col-sm-7">
        {$firstpaymentamount}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.recurringamount}
    </div>
    <div class="col-sm-7">
        {$recurringamount}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.clientareahostingnextduedate}
    </div>
    <div class="col-sm-7">
        {$nextduedate}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.orderbillingcycle}
    </div>
    <div class="col-sm-7">
        {$billingcycle}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.clientareastatus}
    </div>
    <div class="col-sm-7">
        {$status}
    </div>
</div>

{if $suspendreason}
    <div class="row">
        <div class="col-sm-5">
            {$LANG.suspendreason}
        </div>
        <div class="col-sm-7">
            {$suspendreason}
        </div>
    </div>
{/if}

<hr>

<div class="row">
    <div class="col-sm-5">
        Host:
    </div>
    <div class="col-sm-7">
        {$dbhost}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        Port:
    </div>
    <div class="col-sm-7">
        {$port}
    </div>
</div>
<div class="row">
    <div class="col-sm-5">
        User Name:
    </div>
    <div class="col-sm-7">
        {$dbuser}
    </div>
</div>
<div class="row">
    <div class="col-sm-5">
        Password:
    </div>
    <div class="col-sm-7">
        {$dbpassword}
    </div>
</div>
<div class="row">
    <div class="col-sm-5">
        Database Name:
    </div>
    <div class="col-sm-7">
        {$dbname}
    </div>
</div>
<div class="row">
    <div class="col-sm-5">
        phpMyAdmin url:
    </div>
    <div class="col-sm-7">
        <a href="{$adminurl}">{$adminurl}</a>
    </div>
</div>

<hr>
