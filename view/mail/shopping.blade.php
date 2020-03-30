<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            Thư xác nhận đơn hàng
        </td>
    </tr>
    <tr>
        <td style="padding: 20px 0 30px 0;">
            Cảm ơn bạn đã đặt mua hàng . Hệ thống sẽ gọi điện thoại xác nhận đơn hàng sau ít phút
            Đơn hàng gồm có

        </td>
        @foreach($order as $one)
        <td>
                {{$one['name']}} x {{$one['count']}} Đơn giá ${{$one['price']}}
        </td>
        @endforeach
        <td>
            Tổng đơn hàng (đã trừ mã giảm giá) :$ {{$totalCart}}
        </td>

    </tr>
    <tr>
        <td>
            Xin cảm ơn!
        </td>
    </tr>
</table>
