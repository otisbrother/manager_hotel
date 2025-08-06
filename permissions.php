<?php
// Định nghĩa các quyền hạn trong hệ thống
class Permissions {
    // Quyền quản lý dashboard
    const DASHBOARD_VIEW = 'dashboard_view';
    const DASHBOARD_EDIT = 'dashboard_edit';
    
    // Quyền quản lý phòng
    const ROOM_VIEW = 'room_view';
    const ROOM_CREATE = 'room_create';
    const ROOM_EDIT = 'room_edit';
    const ROOM_DELETE = 'room_delete';
    
    // Quyền quản lý đặt phòng
    const BOOKING_VIEW = 'booking_view';
    const BOOKING_CREATE = 'booking_create';
    const BOOKING_EDIT = 'booking_edit';
    const BOOKING_DELETE = 'booking_delete';
    const BOOKING_CONFIRM = 'booking_confirm';
    const BOOKING_CANCEL = 'booking_cancel';
    
    // Quyền quản lý thanh toán
    const PAYMENT_VIEW = 'payment_view';
    const PAYMENT_CREATE = 'payment_create';
    const PAYMENT_EDIT = 'payment_edit';
    const PAYMENT_DELETE = 'payment_delete';
    const PAYMENT_REFUND = 'payment_refund';
    
    // Quyền quản lý nhân viên
    const STAFF_VIEW = 'staff_view';
    const STAFF_CREATE = 'staff_create';
    const STAFF_EDIT = 'staff_edit';
    const STAFF_DELETE = 'staff_delete';
    
    // Quyền quản lý người dùng
    const USER_VIEW = 'user_view';
    const USER_CREATE = 'user_create';
    const USER_EDIT = 'user_edit';
    const USER_DELETE = 'user_delete';
    
    // Quyền quản lý hệ thống
    const SYSTEM_SETTINGS = 'system_settings';
    const SYSTEM_BACKUP = 'system_backup';
    const SYSTEM_LOG = 'system_log';
    
    // Quyền báo cáo
    const REPORT_VIEW = 'report_view';
    const REPORT_EXPORT = 'report_export';
    
    // Quyền truy cập cơ bản cho user
    const USER_ACCESS = 'user_access';
    
    // Lấy tất cả quyền
    public static function getAllPermissions() {
        return [
            self::DASHBOARD_VIEW,
            self::DASHBOARD_EDIT,
            self::ROOM_VIEW,
            self::ROOM_CREATE,
            self::ROOM_EDIT,
            self::ROOM_DELETE,
            self::BOOKING_VIEW,
            self::BOOKING_CREATE,
            self::BOOKING_EDIT,
            self::BOOKING_DELETE,
            self::BOOKING_CONFIRM,
            self::BOOKING_CANCEL,
            self::PAYMENT_VIEW,
            self::PAYMENT_CREATE,
            self::PAYMENT_EDIT,
            self::PAYMENT_DELETE,
            self::PAYMENT_REFUND,
            self::STAFF_VIEW,
            self::STAFF_CREATE,
            self::STAFF_EDIT,
            self::STAFF_DELETE,
            self::USER_VIEW,
            self::USER_CREATE,
            self::USER_EDIT,
            self::USER_DELETE,
            self::SYSTEM_SETTINGS,
            self::SYSTEM_BACKUP,
            self::SYSTEM_LOG,
            self::REPORT_VIEW,
            self::REPORT_EXPORT,
            self::USER_ACCESS
        ];
    }
    
    // Lấy quyền theo nhóm
    public static function getPermissionsByGroup() {
        return [
            'Dashboard' => [
                self::DASHBOARD_VIEW,
                self::DASHBOARD_EDIT
            ],
            'Quản lý phòng' => [
                self::ROOM_VIEW,
                self::ROOM_CREATE,
                self::ROOM_EDIT,
                self::ROOM_DELETE
            ],
            'Quản lý đặt phòng' => [
                self::BOOKING_VIEW,
                self::BOOKING_CREATE,
                self::BOOKING_EDIT,
                self::BOOKING_DELETE,
                self::BOOKING_CONFIRM,
                self::BOOKING_CANCEL
            ],
            'Quản lý thanh toán' => [
                self::PAYMENT_VIEW,
                self::PAYMENT_CREATE,
                self::PAYMENT_EDIT,
                self::PAYMENT_DELETE,
                self::PAYMENT_REFUND
            ],
            'Quản lý nhân viên' => [
                self::STAFF_VIEW,
                self::STAFF_CREATE,
                self::STAFF_EDIT,
                self::STAFF_DELETE
            ],
            'Quản lý người dùng' => [
                self::USER_VIEW,
                self::USER_CREATE,
                self::USER_EDIT,
                self::USER_DELETE
            ],
            'Quản lý hệ thống' => [
                self::SYSTEM_SETTINGS,
                self::SYSTEM_BACKUP,
                self::SYSTEM_LOG
            ],
            'Báo cáo' => [
                self::REPORT_VIEW,
                self::REPORT_EXPORT
            ]
        ];
    }
    
    // Lấy quyền mặc định cho từng role
    public static function getDefaultPermissions($role) {
        switch ($role) {
            case 'super_admin':
                return self::getAllPermissions();
                
            case 'admin':
                return [
                    self::DASHBOARD_VIEW,
                    self::DASHBOARD_EDIT,
                    self::ROOM_VIEW,
                    self::ROOM_CREATE,
                    self::ROOM_EDIT,
                    self::ROOM_DELETE,
                    self::BOOKING_VIEW,
                    self::BOOKING_CREATE,
                    self::BOOKING_EDIT,
                    self::BOOKING_DELETE,
                    self::BOOKING_CONFIRM,
                    self::BOOKING_CANCEL,
                    self::PAYMENT_VIEW,
                    self::PAYMENT_CREATE,
                    self::PAYMENT_EDIT,
                    self::PAYMENT_DELETE,
                    self::PAYMENT_REFUND,
                    self::STAFF_VIEW,
                    self::STAFF_CREATE,
                    self::STAFF_EDIT,
                    self::STAFF_DELETE,
                    self::USER_VIEW,
                    self::USER_CREATE,
                    self::USER_EDIT,
                    self::USER_DELETE,
                    self::REPORT_VIEW,
                    self::REPORT_EXPORT
                ];
                
            case 'manager':
                return [
                    self::DASHBOARD_VIEW,
                    self::ROOM_VIEW,
                    self::ROOM_EDIT,
                    self::BOOKING_VIEW,
                    self::BOOKING_EDIT,
                    self::BOOKING_CONFIRM,
                    self::BOOKING_CANCEL,
                    self::PAYMENT_VIEW,
                    self::PAYMENT_EDIT,
                    self::STAFF_VIEW,
                    self::STAFF_EDIT,
                    self::REPORT_VIEW
                ];
                
            case 'staff':
                return [
                    self::DASHBOARD_VIEW,
                    self::ROOM_VIEW,
                    self::BOOKING_VIEW,
                    self::BOOKING_EDIT,
                    self::PAYMENT_VIEW,
                    self::PAYMENT_EDIT
                ];
                
            case 'user':
                return [
                    self::USER_ACCESS
                ];
                
            default:
                return [];
        }
    }
    
    // Kiểm tra quyền có hợp lệ không
    public static function isValidPermission($permission) {
        return in_array($permission, self::getAllPermissions());
    }
    
    // Lấy tên hiển thị của quyền
    public static function getPermissionName($permission) {
        $names = [
            self::DASHBOARD_VIEW => 'Xem Dashboard',
            self::DASHBOARD_EDIT => 'Chỉnh sửa Dashboard',
            self::ROOM_VIEW => 'Xem phòng',
            self::ROOM_CREATE => 'Tạo phòng mới',
            self::ROOM_EDIT => 'Chỉnh sửa phòng',
            self::ROOM_DELETE => 'Xóa phòng',
            self::BOOKING_VIEW => 'Xem đặt phòng',
            self::BOOKING_CREATE => 'Tạo đặt phòng',
            self::BOOKING_EDIT => 'Chỉnh sửa đặt phòng',
            self::BOOKING_DELETE => 'Xóa đặt phòng',
            self::BOOKING_CONFIRM => 'Xác nhận đặt phòng',
            self::BOOKING_CANCEL => 'Hủy đặt phòng',
            self::PAYMENT_VIEW => 'Xem thanh toán',
            self::PAYMENT_CREATE => 'Tạo thanh toán',
            self::PAYMENT_EDIT => 'Chỉnh sửa thanh toán',
            self::PAYMENT_DELETE => 'Xóa thanh toán',
            self::PAYMENT_REFUND => 'Hoàn tiền',
            self::STAFF_VIEW => 'Xem nhân viên',
            self::STAFF_CREATE => 'Tạo nhân viên',
            self::STAFF_EDIT => 'Chỉnh sửa nhân viên',
            self::STAFF_DELETE => 'Xóa nhân viên',
            self::USER_VIEW => 'Xem người dùng',
            self::USER_CREATE => 'Tạo người dùng',
            self::USER_EDIT => 'Chỉnh sửa người dùng',
            self::USER_DELETE => 'Xóa người dùng',
            self::SYSTEM_SETTINGS => 'Cài đặt hệ thống',
            self::SYSTEM_BACKUP => 'Sao lưu hệ thống',
            self::SYSTEM_LOG => 'Xem log hệ thống',
            self::REPORT_VIEW => 'Xem báo cáo',
            self::REPORT_EXPORT => 'Xuất báo cáo',
            self::USER_ACCESS => 'Truy cập cơ bản'
        ];
        
        return isset($names[$permission]) ? $names[$permission] : $permission;
    }
}
?> 