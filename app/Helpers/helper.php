<?php
use App\Mail\TemplateMail;
use Illuminate\Support\Facades\Mail;

function sendMail($email, $template, $data)
{
    try {
        Mail::to($email)->send(new TemplateMail($data));
        info("Mail sent successfully to $email");
    } catch (\Exception $e) {
        info("Failed to send mail: " . $e->getMessage());
    }
}

if (!function_exists('dateFormat')) {

function dateFormat($date){
return $date->format('d-M-Y');
}
}
if (!function_exists('actions')) {

    function actions($data) {
        $action = '<div class="hstack gap-2 justify-content-end">';

        if (isset($data['edit'])) {

            $action .= '<a href="' . $data['edit'].'" class="avatar-text avatar-md">
                <i class="feather feather-eye"></i>
            </a>';
        }
         if (isset($data['view']) || isset($data['delete'])) {
            $action .= '<div class="dropdown">
                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                    <i class="feather feather-more-horizontal"></i>
                </a>
                <ul class="dropdown-menu">';

            if (isset($data['view'])) {
                $viewUrl = $data['view'];
                $action .= '<li>
                    <a class="dropdown-item" href="' . $viewUrl . '">
                        <i class="feather feather-eye me-3"></i>
                        <span>View</span>
                    </a>
                </li>';
            }

            if (isset($data['delete'])) {
                $editUrl = $data['delete'];
                $action .= '<li>
                    <a class="dropdown-item" href="' . $editUrl . '">
                        <i class="feather feather-edit-3 me-3"></i>
                        <span>Edit</span>
                    </a>
                </li>';
            }

            $action .= '<li>
                <a class="dropdown-item printBTN" href="javascript:void(0)">
                    <i class="feather feather-printer me-3"></i>
                    <span>Print</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="javascript:void(0)">
                    <i class="feather feather-clock me-3"></i>
                    <span>Remind</span>
                </a>
            </li>
            <li class="dropdown-divider"></li>';

            if (isset($data['archive'])) {
                $action .= '<li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="feather feather-archive me-3"></i>
                        <span>Archive</span>
                    </a>
                </li>';
            }

            if (isset($data['report_spam'])) {
                $action .= '<li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="feather feather-alert-octagon me-3"></i>
                        <span>Report Spam</span>
                    </a>
                </li>';
            }

            if (isset($data['delete'])) {
                $action .= '<li class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="feather feather-trash-2 me-3"></i>
                        <span>Delete</span>
                    </a>
                </li>';
            }

            $action .= '</ul>
            </div>';
        }

        $action .= '</div>';
        return $action;
    }
}
if (!function_exists('isActiveInactive')) {
    function isActiveInactive($status, $route, $id) {
        // Determine if the toggle should be checked
        $isChecked = $status == '1' ? 'checked' : '';

        return '
        <div class="form-check form-switch form-switch-sm">
            <input class="form-check-input c-pointer statusChange" type="checkbox" id="formSwitch'.$id.'" '.$isChecked.' data-id="'.$id.'" data-url="'.$route.'">
            <label class="form-check-label fw-500 text-dark c-pointer" for="formSwitch'.$id.'">
                ' . ($status == '1' ? 'Active' : 'Inactive') . '
            </label>
        </div>
        ';
    }
}

/*
        <select class="form-control statusChange" data-id="'.$id.'" data-url="'.$route.'" required name="status" style="display: none;">
            <option value="1" '.($status == '1' ? 'selected' : '').'>Active</option>
            <option value="0" '.($status == '0' ? 'selected' : '').'>Inactive</option>
        </select> */
if (!function_exists('shopStatusSelect')) {
    // Shop status is a 3-state enum (pending/approved/rejected), so a toggle can't represent it.
    function shopStatusSelect($status, $route, $shopId) {
        if (empty($shopId)) {
            return '<span class="badge bg-soft-dark text-dark"><i class="feather feather-slash me-1"></i>No Shop</span>';
        }

        $states = [
            'pending'  => ['label' => 'Pending',  'color' => 'warning', 'icon' => 'feather-clock'],
            'approved' => ['label' => 'Approved', 'color' => 'success', 'icon' => 'feather-check-circle'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger',  'icon' => 'feather-x-circle'],
        ];
        $current = $states[$status] ?? $states['pending'];

        $items = '';
        foreach ($states as $value => $state) {
            if ($value === $status) {
                continue;
            }
            $items .= '<li>
                <a class="dropdown-item c-pointer shopStatusChange" href="javascript:void(0)"
                   data-id="' . $shopId . '" data-url="' . $route . '" data-status="' . $value . '">
                    <i class="feather ' . $state['icon'] . ' me-3 text-' . $state['color'] . '"></i>
                    <span>' . $state['label'] . '</span>
                </a>
            </li>';
        }

        return '
        <div class="dropdown">
            <a href="javascript:void(0)" class="badge bg-soft-' . $current['color'] . ' text-' . $current['color'] . ' c-pointer"
               data-bs-toggle="dropdown" data-bs-offset="0,10">
                <i class="feather ' . $current['icon'] . ' me-1"></i>
                <span>' . $current['label'] . '</span>
                <i class="feather feather-chevron-down ms-1"></i>
            </a>
            <ul class="dropdown-menu">' . $items . '</ul>
        </div>';
    }
}

if (!function_exists('statusChange')) {
 function statusChange($request,$modalName)
{
    // Extract data from request
    $status = $request->input('status');
    $id = $request->input('id');

    // Find the record and update status
    $record = $modalName::find($id); // Replace 'YourModel' with the actual model name

    if ($record) {
        $record->status = $status;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Record not found.',
        ], 404);
    }
}
}


