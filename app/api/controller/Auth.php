<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\User;
use app\common\model\Comment;
use app\common\model\BrowseHistory;
use app\common\model\Favorite;
use app\common\model\Like;
use app\common\util\Jwt;

class Auth extends BaseController
{
    use ApiResponse;

    /**
     * 用户注册
     * POST /api/auth/register
     */
    public function register()
    {
        $data = $this->request->post();
        $rules = [
            'username' => 'require|alphaDash|length:3,50|unique:users',
            'password' => 'require|length:6,32',
        ];
        $messages = [
            'username.require' => '请输入用户名',
            'username.alphaDash' => '用户名只能包含字母、数字、下划线和破折号',
            'username.length' => '用户名长度3-50个字符',
            'username.unique' => '用户名已存在',
            'password.require' => '请输入密码',
            'password.length' => '密码长度6-32个字符',
        ];
        $validate = new \think\Validate($rules, $messages);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        $user = User::create([
            'username' => $data['username'],
            'password' => sha1($data['password']),
            'nickname' => $data['nickname'] ?? $data['username'],
            'email' => $data['email'] ?? null,
        ]);

        $token = Jwt::encode([
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return $this->success([
            'token' => $token,
            'user' => $user,
        ], '注册成功');
    }

    /**
     * 用户登录
     * POST /api/auth/login
     * Body: { username, password, captcha_key, captcha }
     */
    public function login()
    {
        $data = $this->request->post();

        // 验证码校验
        if (!CaptchaController::verify($data['captcha_key'] ?? '', $data['captcha'] ?? '')) {
            return $this->error('验证码错误或已过期');
        }

        if (empty($data['username']) || empty($data['password'])) {
            return $this->error('请输入用户名和密码');
        }

        $user = User::where('username', $data['username'])->find();
        if (!$user || sha1($data['password']) !== $user->password) {
            return $this->error('用户名或密码错误');
        }

        if ($user->status !== 1) {
            return $this->error('账号已被禁用');
        }

        $user->last_login_ip = $this->request->ip();
        $user->last_login_at = date('Y-m-d H:i:s');
        $user->save();

        $token = Jwt::encode([
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return $this->success([
            'token' => $token,
            'user' => $user,
        ], '登录成功');
    }

    /**
     * 获取当前用户信息
     * GET /api/user/profile
     */
    public function profile()
    {
        $user = User::find($this->request->user_id);
        if (!$user) {
            return $this->error('用户不存在');
        }

        // 附加统计信息
        $userId = $user->id;
        $data = $user->toArray();
        $data['comment_count'] = Comment::where('user_id', $userId)->count();
        $data['favorite_count'] = Favorite::where('user_id', $userId)->where('target_type', 'article')->count();
        $data['like_count'] = Like::where('user_id', $userId)->where('target_type', 'article')->count();

        return $this->success($data);
    }

    /**
     * 更新用户资料
     * PUT /api/user/profile
     */
    public function updateProfile()
    {
        $data = $this->request->post();
        $user = User::find($this->request->user_id);
        if (!$user) {
            return $this->error('用户不存在');
        }

        $allowFields = ['nickname', 'email', 'mobile', 'avatar', 'bio'];
        $updateData = array_intersect_key($data, array_flip($allowFields));

        $user->save($updateData);
        return $this->success($user, '更新成功');
    }

    /**
     * 修改密码
     * PUT /api/user/password
     */
    public function changePassword()
    {
        $data = $this->request->post();
        if (empty($data['old_password']) || empty($data['new_password'])) {
            return $this->error('请输入旧密码和新密码');
        }

        $user = User::find($this->request->user_id);
        if (!$user) return $this->error('用户不存在');

        if (sha1($data['old_password']) !== $user->password) {
            return $this->error('旧密码不正确');
        }

        if (strlen($data['new_password']) < 6 || strlen($data['new_password']) > 32) {
            return $this->error('新密码长度6-32个字符');
        }

        $user->password = sha1($data['new_password']);
        $user->save();

        return $this->success(null, '密码修改成功');
    }

    /**
     * 我的评论列表
     * GET /api/user/comments?page=1&page_size=10
     */
    public function myComments()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);

        $query = Comment::where('user_id', $this->request->user_id)
            ->order('created_at', 'desc');

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 我的浏览记录
     * GET /api/user/browse-history?page=1&page_size=10
     */
    public function browseHistory()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);

        $query = BrowseHistory::where('user_id', $this->request->user_id)
            ->order('created_at', 'desc');

        return $this->paginate($query, $page, $pageSize);
    }
}
