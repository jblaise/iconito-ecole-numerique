
<div class="minimail_navig">
    {if $message->prev}
        <a href="{copixurl dest="|getMessage" id=$message->prev}" class="floatleft">< {i18n key="minimail.msg.previous"}</a>
    {/if}
    {if $message->next}
        <a href="{copixurl dest="|getMessage" id=$message->next}" class="floatright">{i18n key="minimail.msg.next"} ></a>
    {/if}
    <div class="center">
        {if $message->type eq "recv"}
            <a href="{copixurl dest="|getListRecv"}">{i18n key="minimail.msg.backList"}</a>
        {else}
            <a href="{copixurl dest="|getListSend"}">{i18n key="minimail.msg.backList"}</a>
        {/if}
    </div>
</div>
<div class="clear"></div>

<div class="minimail_message">

<div class="minimail_header">
    {if $message->avatar}<img src="{copixurl}{$message->avatar}" alt="{$message->avatar}" title="{$message->from.login}" align="right" hspace="2" vspace="2" />{/if}

    <strong>{i18n key="minimail.msg.from"}</strong>

    {user label=$message->from_id_infos userType=$message->from.type userId=$message->from.id}, <strong>{i18n key="minimail.msg.to"}</strong> {assign var=sep value=""}{foreach from=$dest item=to}{$sep}{user label=$to->to_id_infos userType=$to->to.type userId=$to->to.id}{assign var=sep value=", "}{/foreach}, <strong>{i18n key="minimail.msg.date}</strong> {$message->date_send|datei18n:"date_short_time"}
</div>

<div class="render">
  {$message->message|render:$message->format}
</div>
{if $message->attachment1}
    <DIV CLASS="minimail_attachment">
    <strong>{i18n key="minimail.msg.attach1}</strong> : <a href="{copixurl dest="|downloadAttachment"  file=$message->attachment1|escape}">{$message->attachment1Name}</a>
    {if $message->attachment1IsImage }<br/><a href="{copixurl dest="|downloadAttachment"  file=$message->attachment1|escape}"><img width="100" border="0" src="{copixurl dest="|previewAttachment" file=$message->attachment1}"></a>{/if}
    </DIV>
{/if}
{if $message->attachment2}
    <DIV CLASS="minimail_attachment">
    <strong>{i18n key="minimail.msg.attach2}</strong> : <a href="{copixurl dest="|downloadAttachment"  file=$message->attachment2|escape}">{$message->attachment2Name}</a>
    {if $message->attachment2IsImage }<br/><a href="{copixurl dest="|downloadAttachment"  file=$message->attachment2|escape}"><img width="100" border="0" src="{copixurl dest="|previewAttachment" file=$message->attachment2}"></a>{/if}
    </DIV>
{/if}
{if $message->attachment3}
    <DIV CLASS="minimail_attachment">
    <strong>{i18n key="minimail.msg.attach3}</strong> : <a href="{copixurl dest="|downloadAttachment"  file=$message->attachment3|escape}">{$message->attachment3Name}</a>
    {if $message->attachment3IsImage }<br/><a href="{copixurl dest="|downloadAttachment"  file=$message->attachment3|escape}"><img width="100" border="0" src="{copixurl dest="|previewAttachment" file=$message->attachment3}"></a>{/if}
    </DIV>
{/if}
<br clear="all" />
</div>

<span class="floatleft">


{if $message->getNbAttachments() > 0}

        <a href="{copixurl dest="|attachmentToClasseur" id=$message->id}" class="button button-move fancyframe" id="buttonAttachmentToClasseur">{i18n key="minimail.attachmentToClasseur.action" pNb=$message->getNbAttachments()}</a>

{/if}
</span>

{iconitominimail_hasuseraccess assign=has_user_access}
{if $has_user_access}
    <p class="right">
        {if $message->type eq "recv"}
          <input style="margin:2px;" class="button button-continue" onclick="self.location='{copixurl dest="|getNewForm" reply=$message->id}'" type="button" value="{i18n key="minimail.btn.reply}" />

          {if $dest|@count>1}
            <input style="margin:2px;" class="button button-continue" onclick="self.location='{copixurl dest="|getNewForm" reply=$message->id all=1}'" type="button" value="{i18n key="minimail.btn.replyAll}" />
          {/if}
        {/if}

        <input style="margin:2px;" class="button button-continue" onclick="self.location='{copixurl dest="|getNewForm" forward=$message->id}'" type="button" value="{i18n key="minimail.btn.forward}" />

    </p>

{/if}

