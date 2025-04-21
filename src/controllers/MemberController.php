<//?php

namespace App\Controllers;

use App\Models\Member;

class MemberController
{
    protected $memberModel;

    public function __construct()
    {
        $this->memberModel = new Member();
    }

    public function listMembers()
    {
        $members = $this->memberModel->fetchAllMembers();
        // Code to render the members view with the list of members
    }

    public function filterMembers($criteria)
    {
        $filteredMembers = $this->memberModel->fetchMembersByCriteria($criteria);
        // Code to render the members view with the filtered list of members
    }

    public function addMember($data)
    {
        $this->memberModel->saveMember($data);
        // Code to redirect or render a success message
    }

    public function editMember($id, $data)
    {
        $this->memberModel->updateMember($id, $data);
        // Code to redirect or render a success message
    }

    public function deleteMember($id)
    {
        $this->memberModel->removeMember($id);
        // Code to redirect or render a success message
    }
}
