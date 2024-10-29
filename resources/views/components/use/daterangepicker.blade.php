<div id="reportrange-{{$position}}" class="reportrange" atributo="{{$attr ?? ''}}" position="{{$position ?? 0}}">
    <i class="fa fa-calendar"></i>&nbsp;
    <span></span> <i class="fa fa-caret-down"></i>
    <input type="hidden" class="inputReportrange" name="startDate[{{$position ?? 0}}]">
    <input type="hidden" class="inputReportrange" name="endDate[{{$position ?? 0}}]">
</div>