<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\UploadFile;

class UploadController extends BaseController
{
    use ApiResponse;

    /**
     * 文件上传（需要用户登录）
     * POST /api/user/upload
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('请选择文件');
        }

        $allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip', 'rar'];
        $ext = strtolower($file->getOriginalExtension());
        if (!in_array($ext, $allowExt)) {
            return $this->error('不支持的文件类型: ' . $ext);
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            return $this->error('文件大小不能超过10MB');
        }

        try {
            $savePath = app()->getRootPath() . 'public/uploads/' . date('Ym') . '/';
            if (!is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }

            $saveName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $file->move($savePath, $saveName);

            $fileUrl = '/uploads/' . date('Ym') . '/' . $saveName;

            $record = UploadFile::create([
                'user_id' => $this->request->user_id ?? null,
                'original_name' => $file->getOriginalName(),
                'file_name' => $saveName,
                'file_path' => date('Ym') . '/' . $saveName,
                'file_url' => $fileUrl,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getOriginalMime(),
                'extension' => $ext,
                'storage' => 'local',
            ]);

            return $this->success($record, '上传成功');
        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }
}
