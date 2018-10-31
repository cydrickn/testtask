<?php
declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpListMember;
use App\Http\Controllers\Controller;
use App\Services\MailChimp\ListMemberService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
class ListMembersController extends Controller
{
    /**
     * List member service
     *
     * @var ListMemberService
     */
    private $listMemberService;

    /**
     * ListMembersController constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param ListMemberService $listMemberService
     */
    public function __construct(EntityManagerInterface $entityManager, ListMemberService $listMemberService)
    {
        parent::__construct($entityManager);
        $this->listMemberService = $listMemberService;
    }

    /**
     * Create list member
     *
     * @param Request $request
     * @param string $listId
     *
     * @return JsonResponse
     */
    public function create(Request $request, string $listId): JsonResponse
    {
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);

        if ($list === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpList [%s] not found', $listId)],
                404
            );
        }

        try {
            $member = new MailChimpListMember($request->all());
            $this->listMemberService->addMemberToList($list, $member);
        } catch (ValidationException $validationException) {
            return $this->validationErrorResponse([
                'message' => 'Invalid data given',
                'errors' => $validationException->validator->errors()->toArray(),
            ]);
        } catch (\Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }

    /**
     * Update list member
     *
     * @param Request $request
     * @param string $listId
     * @param string $memberId
     *
     * @return JsonResponse
     */
    public function update(Request $request, string $listId, string $memberId): JsonResponse
    {
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);

        if ($list === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpList [%s] not found', $listId)],
                404
            );
        }

        $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpListMember [%s] not found', $memberId)],
                404
            );
        }

        try {
            $this->listMemberService->updateListMember($member, $request->all());
        } catch (ValidationException $validationException) {
            return $this->validationErrorResponse([
                'message' => 'Invalid data given',
                'errors' => $validationException->validator->errors()->toArray(),
            ]);
        } catch (\Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }

    public function remove(string $listId, string $memberId): JsonResponse
    {
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);

        if ($list === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpList [%s] not found', $listId)],
                404
            );
        }

        $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpListMember [%s] not found', $memberId)],
                404
            );
        }

        try {
            $this->listMemberService->removeListMember($member);
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse([]);
    }

    public function show(string $listId, string $memberId): JsonResponse
    {
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);

        if ($list === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpList [%s] not found', $listId)],
                404
            );
        }

        $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpListMember [%s] not found', $memberId)],
                404
            );
        }

        return $this->successfulResponse($member->toArray());
    }

    public function showList(Request $request, string $listId): JsonResponse
    {
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);

        if ($list === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpList [%s] not found', $listId)],
                404
            );
        }
        $offset = $request->query('offset', 0);
        $count = $request->query('count', 10);
        try {
            $members = $this->listMemberService->retrieveMembersFromList($list, $offset, $count);
        } catch (\Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        $response = [
            'offset' => $offset,
            'count' => $count,
            'members' => array_map(function ($member) {
                return $member->toArray();
            }, $members),
        ];

        return $this->successfulResponse($response);
    }
}
