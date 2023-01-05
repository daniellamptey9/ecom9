<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Hash;
use App\Models\Admin; 
use Brian2694\Toastr\Facades\Toastr;
use Image;

class AdminController extends Controller
{
    public function dashboard(){
        return view('admin.dashboard');
    }

    public function login(Request $request){

        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ];

            $customMessage = [
                //Add Custom Messages here
                'email.required' => 'Email Address is required!',
                'email.email' => 'Valid Email Address is required',
                'password.required' => 'Password is required',
            ];

            $this->validate($request,$rules,$customMessage);

            if (Auth::guard('mantse')->attempt(['email'=>$data['email'],'password'=>$data['password'],'status'=>1])) {
                 Toastr::success('Welcome, login successfully!');

                return redirect('mantse/dashboard');
            }else{
                Toastr::error('Invalid Email or Password!');
                return redirect()->back();
            }
        }
        return view('admin.login');
    }


    public function updateAdminPassword(Request $request){
        if($request->isMethod('POST')){
            $data = $request->all();
            // check if current password entered is correct
            if (Hash::check($data['current_password'],Auth::guard('mantse')->user()->password)) {
                // check if new password is matching with confrim password
                if ($data['confirm_password']==$data['new_password']) {
                    Admin::where('id',Auth::guard('mantse')->user()->id)->update(['password'=>bcrypt($data['new_password'])]);
                    Toastr::success('Password updated successfully!');

                     return redirect('mantse/login');
                }else{
                     Toastr::error('New Password not matching confirm password!');
                 return redirect()->back();
                }
            }else{
                 Toastr::error('Your current password is Incorrect!');
                return redirect()->back();
            }
        }
        $adminDetails = Admin::where('email',Auth::guard('mantse')->user()->email)->first()->toArray();
        return view('admin.settings.update_admin_password')->with(compact('adminDetails'));
    }





    public function checkAdminPassword(Request $request){
        $data = $request->all();
        if (Hash::check($data['current_password'],Auth::guard('mantse')->user()->password)) {
            return "true";
        }else{
            return "false";
        }
    }


    public function updateAdminDetails(Request $request){
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'admin_name' => 'required|regex:/^[\pL\s\-]+$/u',
                'admin_mobile' => 'required|numeric',
            ];

            $this->validate($request,$rules);

            // Upload Admin Photo
            if ($request->hasFile('admin_image')) {
                $image_tmp = $request->file('admin_image');
                if ($image_tmp->isValid()) {
                    // get image extension
                    $extension = $image_tmp->getClientOriginalExtension();
                    // Generate New Image Name
                    $imageName = rand(111,99999).'.'.$extension;
                    $imagePath = 'admin/assets/img/photos/'.$imageName;
                    // Upload Image
                    Image::make($image_tmp)->save($imagePath);
                }
            }else if(!empty($data['current_admin_image'])){
                $imageName = $data['current_admin_image'];
            }else{
                $imageName = "";
            }


            //Update Admin Details
            Admin::where('id',Auth::guard('mantse')->user()->id)->update(['name'=>$data['admin_name'],'mobile'=>$data['admin_mobile'],'image'=>$imageName]);
            Toastr::success('Admin Details Updated Successfully!');
            return redirect()->back();
        }
        return view('admin.settings.update_admin_details');
    }




    public function logout(){
        Auth::guard('mantse')->logout();
        return redirect('mantse/login');
    }





}
