<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\UploadFile;

class UploadController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = UploadFile::order('id', 'desc');
        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 上传文件（管理员）
     * POST /api/admin/upload
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) return $this->error('请选择文件');

        $allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip', 'rar', 'mp4', 'mp3'];
        $ext = strtolower($file->getOriginalExtension());
        if (!in_array($ext, $allowExt)) {
            return $this->error('不支持的文件类型: ' . $ext);
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
                'admin_id' => $this->request->admin_id,
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

    public function delete($id)
    {
        $file = UploadFile::find($id);
        if (!$file) return $this->error('文件不存在', 404);

        $fullPath = app()->getRootPath() . 'public/uploads/' . $file->file_path;
        if (file_exists($fullPath)) unlink($fullPath);

        $file->delete();
        return $this->success(null, '删除成功');
    }
}
