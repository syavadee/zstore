<table class="ctable" border="0" cellpadding="2" cellspacing="0">

    <tr style="font-size:larger; font-weight: bolder;">
        <td align="center" colspan="9">
            Состояние  складов
        </td>
    </tr>
 

    <tr style="font-weight: bolder;">

        <th style="border: solid black 1px">Наименование</th>
        <th style="border: solid black 1px">Артикул</th>
        {{#stores}}
        <th style="border: solid black 1px">{{name}} </th>
        {{/stores}}
    </tr>
    {{#_detail}}       
    <tr>

        <td>{{itemname}}</td>
        <td>{{item_code}}</td>
        {{#stlist}}
          <td align="right">{{qty}}</td>
        {{/stlist}}
    </tr>
    {{/_detail}}
   
</table>


